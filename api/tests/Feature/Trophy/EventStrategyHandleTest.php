<?php


namespace Tests\Feature\Trophy;


use App\Exceptions\Projection\ProjectionException;
use App\Listeners\Projection\TrophyProjectorListener;
use App\Models\ReadModels\Trophy;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TrophyRepository;
use App\Services\BrokerCommandStrategy\TrophyUpdateInfo;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
use App\Services\Cache\TrophyCacheService;
use App\Services\EventStrategy\TeamBecameRunnerUp;
use App\Services\EventStrategy\TeamBecameWinner;
use App\ValueObjects\Broker\CommandQuery\Headers;
use App\ValueObjects\Broker\CommandQuery\Message as CommandQueryMessage;
use App\ValueObjects\Broker\Mediator\Message;
use Carbon\Carbon;
use Faker\Factory;
use TestCase;
use Tests\Traits\AmazonBrokerTrait;
use Tests\Traits\TeamRepositoryTestTrait;
use Tests\Traits\TrophyRepositoryTestTrait;


/**
 * Class EventStrategyHandleTest
 * @package Tests\Feature\Trophy
 */
class EventStrategyHandleTest extends TestCase
{
	use AmazonBrokerTrait,
		TrophyRepositoryTestTrait,
		TeamRepositoryTestTrait;

	private \Faker\Generator $faker;
	private $serializer;
	private TrophyRepository $trophyRepository;
	private BrokerMessageCacheServiceInterface $brokerMessageCacheService;
	private TeamRepository $teamRepository;
	private TrophyCacheService $trophyCacheService;

	protected function setUp(): void
	{
		$this->createApplication();
		$this->faker = Factory::create();
		$this->serializer = app('Serializer');
		$this->trophyRepository = app(TrophyRepository::class);
		$this->trophyCacheService = app(TrophyCacheService::class);
		$this->teamRepository = app(TeamRepository::class);
		$this->brokerMessageCacheService = app(BrokerMessageCacheServiceInterface::class);
		$this->setupAWSBroker();
		$this->createTrophyTable();
		$this->createTeamTable();
	}

	public function testTeamBecameWinnerHandle()
	{
		$winnerMessage = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s"
            },
			"body":{
				"identifiers": {
					"competition": "%s",
					"tournament": "%s",
					"team": "%s"
				 },
				"metadata": {}
			}
		}',
			config('mediator-event.events.team_became_winner'),
			Carbon::now()->toDateTimeString(),
			$this->faker->uuid,
			$this->faker->uuid,
			$this->faker->uuid);
		$runnerUpMessage = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s"
            },
			"body":{
				"identifiers": {
					"competition": "%s",
					"tournament": "%s",
					"team": "%s"
				 },
				"metadata": {}
			}
		}',
			config('mediator-event.events.team_became_runner_up'),
			Carbon::now()->toDateTimeString(),
			$this->faker->uuid,
			$this->faker->uuid,
			$this->faker->uuid);
		/**
		 * @var Message $winnerMessage
		 */
		$winnerMessage = app('Serializer')->deserialize($winnerMessage, Message::class, 'json');
		/**
		 * @var Message $runnerUpMessage
		 */
		$runnerUpMessage = app('Serializer')->deserialize($runnerUpMessage, Message::class, 'json');
		/**
		 * persist team item.
		 */
		$fakeTeamModelWinner = $this->createTeamModel()
			->setId($winnerMessage->getBody()->getIdentifiers()['team']);
		$this->teamRepository->persist($fakeTeamModelWinner);
		$fakeTeamModelRunner = $this->createTeamModel()
			->setId($runnerUpMessage->getBody()->getIdentifiers()['team']);
		$this->teamRepository->persist($fakeTeamModelRunner);
		/**
		 * Handle event.
		 */
		app(TeamBecameWinner::class)->handle($winnerMessage->getBody());
		app(TeamBecameRunnerUp::class)->handle($runnerUpMessage->getBody());
		/**
		 * Read from DB
		 * @var Trophy $trophy
		 */
		$trophies = $this->trophyRepository->findAll();
		$winner = ($trophies[0]->getPosition() == Trophy::POSITION_WINNER) ? $trophies[0]: $trophies[1];
		$runner = ($trophies[0]->getPosition() == Trophy::POSITION_RUNNER_UP) ? $trophies[0]: $trophies[1];

		$this->assertEquals($winnerMessage->getBody()->getIdentifiers()['team'], $winner->getTeamId());
		$this->assertEquals($fakeTeamModelWinner->getName()->getOfficial(), $winner->getTeamName());
		$this->assertEquals($winnerMessage->getBody()->getIdentifiers()['tournament'], $winner->getTournamentId());
		$this->assertEquals($winnerMessage->getBody()->getIdentifiers()['competition'], $winner->getCompetitionId());
		$this->assertNull($winner->getCompetitionName());
		$this->assertEquals("0", $winner->getTournamentSeason());
		$this->assertEquals(Trophy::POSITION_WINNER, $winner->getPosition());
		$this->assertNotNull($winner->getSortKey());

		$this->assertEquals($runnerUpMessage->getBody()->getIdentifiers()['team'], $runner->getTeamId());
		$this->assertEquals($fakeTeamModelRunner->getName()->getOfficial(), $runner->getTeamName());
		$this->assertEquals($runnerUpMessage->getBody()->getIdentifiers()['tournament'], $runner->getTournamentId());
		$this->assertEquals($runnerUpMessage->getBody()->getIdentifiers()['competition'], $runner->getCompetitionId());
		$this->assertNull($runner->getCompetitionName());
		$this->assertEquals("0", $runner->getTournamentSeason());
		$this->assertEquals(Trophy::POSITION_RUNNER_UP, $runner->getPosition());
		$this->assertNotNull($runner->getSortKey());

		/**
		 * Consume question message for get competition info from competition_service.
		 */
		$response = $this->brokerService->consumePureMessage([config('broker.queues.question')], 10);
		$this->assertNotEmpty($response);
		$playerMessage_winner = json_decode(json_encode($response[0]), true);
		$payload_winner = json_decode($playerMessage_winner, true);
		$this->assertEquals(config('broker.services.team_name'), $payload_winner['headers']['source']);
		$this->assertEquals(config('broker.services.competition_name'), $payload_winner['headers']['destination']);
		$this->assertEquals(TrophyProjectorListener::BROKER_EVENT_KEY, $payload_winner['headers']['key']);
		$this->assertNotNull($payload_winner['headers']['id']);
		$this->assertEquals(config('broker.services.tournament_name'), $payload_winner['body']['entity']);
		$this->assertNotNull($payload_winner['body']['id']);

		$playerMessage_runner = json_decode(json_encode($response[1]), true);
		$payload_runner = json_decode($playerMessage_runner, true);
		$this->assertEquals(config('broker.services.team_name'), $payload_runner['headers']['source']);
		$this->assertEquals(config('broker.services.competition_name'), $payload_runner['headers']['destination']);
		$this->assertEquals(TrophyProjectorListener::BROKER_EVENT_KEY, $payload_runner['headers']['key']);
		$this->assertNotNull($payload_runner['headers']['id']);
		$this->assertEquals(config('broker.services.tournament_name'), $payload_runner['body']['entity']);
		$this->assertNotNull($payload_runner['body']['id']);

		/**
		 * Produce answer message from player service for update player info in transfer model.
		 */
		$answerMessageWinner = (new CommandQueryMessage())
			->setHeaders(
				(new Headers())
					->setKey(TrophyProjectorListener::BROKER_EVENT_KEY)
					->setId($payload_winner['headers']['id'])
					->setDestination(config('broker.services.team_name'))
					->setSource(config('broker.services.competition_name'))
					->setDate(Carbon::now()->toDateTimeString())
			)->setBody([
				'entity' => config('broker.services.tournament_name'),
				'id' => $payload_winner['body']['id'],
				'competitionName' => $this->faker->name,
				'season' => '2020-2021'
			]);

		$answerMessageRunner = (new CommandQueryMessage())
			->setHeaders(
				(new Headers())
					->setKey(TrophyProjectorListener::BROKER_EVENT_KEY)
					->setId($payload_runner['headers']['id'])
					->setDestination(config('broker.services.team_name'))
					->setSource(config('broker.services.competition_name'))
					->setDate(Carbon::now()->toDateTimeString())
			)->setBody([
				'entity' => config('broker.services.tournament_name'),
				'id' => $payload_runner['body']['id'],
				'competitionName' => $this->faker->name,
				'season' => '2020-2021'
			]);
		/**
		 * Handle answer message from competition service for update trophy info in trophy model.
		 */
		app(TrophyUpdateInfo::class)->handle($answerMessageWinner);
		app(TrophyUpdateInfo::class)->handle($answerMessageRunner);
		/**
		 * Read from DB
		 * @var Trophy $trophy
		 */
		$trophies = $this->trophyRepository->findAll();
		$winner = ($trophies[0]->getPosition() == Trophy::POSITION_WINNER) ? $trophies[0]: $trophies[1];
		$runner = ($trophies[0]->getPosition() == Trophy::POSITION_RUNNER_UP) ? $trophies[0]: $trophies[1];
		$this->assertEquals($answerMessageWinner->getBody()['competitionName'], $winner->getCompetitionName());
		$this->assertEquals($answerMessageWinner->getBody()['season'], $winner->getTournamentSeason());
		$this->assertEquals($answerMessageRunner->getBody()['competitionName'], $runner->getCompetitionName());
		$this->assertEquals($answerMessageRunner->getBody()['season'], $runner->getTournamentSeason());
		/**
		 * Check broker message cache for trophy info.
		 */
		$brokerMessageCache = $this->brokerMessageCacheService->getTournamentInfo($winnerMessage->getBody()->getIdentifiers()['tournament']);
		$this->assertEquals($answerMessageWinner->getBody()['competitionName'], $brokerMessageCache['competitionName']);
		$this->assertEquals($answerMessageWinner->getBody()['season'], $brokerMessageCache['season']);
		$brokerMessageCache = $this->brokerMessageCacheService->getTournamentInfo($runnerUpMessage->getBody()->getIdentifiers()['tournament']);
		$this->assertEquals($answerMessageRunner->getBody()['competitionName'], $brokerMessageCache['competitionName']);
		$this->assertEquals($answerMessageRunner->getBody()['season'], $brokerMessageCache['season']);
		/**
		 * Read from cache.
		 */
		$response = app('cache')->get($this->trophyCacheService->getTrophyByTeamKey($runnerUpMessage->getBody()->getIdentifiers()['team']));
		$this->assertInstanceOf(Trophy::class, $response[0]);
		$response = app('cache')->get($this->trophyCacheService->getTrophyByTeamKey($winnerMessage->getBody()->getIdentifiers()['team']));
		$this->assertInstanceOf(Trophy::class, $response[0]);
		$response = app('cache')->get($this->trophyCacheService->getTrophyByCompetitionKey($winnerMessage->getBody()->getIdentifiers()['competition']));
		$this->assertInstanceOf(Trophy::class, $response[0]);
		$response = app('cache')->get($this->trophyCacheService->getTrophyByCompetitionKey($runnerUpMessage->getBody()->getIdentifiers()['competition']));
		$this->assertInstanceOf(Trophy::class, $response[0]);
	}

	public function testTeamBecameWinnerHandleWithNullIdentifier()
	{
		$this->expectException(ProjectionException::class);
		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s"
            },
			"body":{
				"identifiers": {
					"competition": "",
					"tournament": "",
					"team": ""
				 },
				"metadata": {}
			}
		}',
			config('mediator-event.events.team_became_winner'),
			Carbon::now()->toDateTimeString());
		/**
		 * @var Message $message
		 */
		$message = app('Serializer')->deserialize($message, Message::class, 'json');
		/**
		 * Handle event.
		 */
		app(TeamBecameWinner::class)->handle($message->getBody());
	}

	public function testTeamBecameWinnerHandleWhenTeamNotExist()
	{
		$this->expectException(ProjectionException::class);
		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s"
            },
			"body":{
				"identifiers": {
					"competition": "%s",
					"tournament": "%s",
					"team": "%s"
				 },
				"metadata": {}
			}
		}',
			config('mediator-event.events.team_became_winner'),
			Carbon::now()->toDateTimeString(),
			$this->faker->uuid,
			$this->faker->uuid,
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = app('Serializer')->deserialize($message, Message::class, 'json');
		/**
		 * Handle event.
		 */
		app(TeamBecameWinner::class)->handle($message->getBody());
	}

	public function testTeamBecameWinnerHandleWhenCompetitionInfoIsEmpty()
	{
		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s"
            },
			"body":{
				"identifiers": {
					"competition": "%s",
					"tournament": "%s",
					"team": "%s"
				 },
				"metadata": {}
			}
		}',
			config('mediator-event.events.team_became_winner'),
			Carbon::now()->toDateTimeString(),
			$this->faker->uuid,
			$this->faker->uuid,
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = app('Serializer')->deserialize($message, Message::class, 'json');
		/**
		 * persist team item.
		 */
		$fakeTeamModel = $this->createTeamModel()
			->setId($message->getBody()->getIdentifiers()['team']);
		$this->teamRepository->persist($fakeTeamModel);
		/**
		 * Handle event.
		 */
		app(TeamBecameWinner::class)->handle($message->getBody());
		/**
		 * Read from DB
		 * @var Trophy $trophy
		 */
		$trophies = $this->trophyRepository->findAll();
		foreach ($trophies as $trophy) {
			$this->assertEquals($message->getBody()->getIdentifiers()['team'], $trophy->getTeamId());
			$this->assertEquals($fakeTeamModel->getName()->getOfficial(), $trophy->getTeamName());
			$this->assertEquals($message->getBody()->getIdentifiers()['tournament'], $trophy->getTournamentId());
			$this->assertEquals($message->getBody()->getIdentifiers()['competition'], $trophy->getCompetitionId());
			$this->assertNull($trophy->getCompetitionName());
			$this->assertEquals("0", $trophy->getTournamentSeason());
			$this->assertEquals(Trophy::POSITION_WINNER, $trophy->getPosition());
			$this->assertNotNull($trophy->getSortKey());
		}
		/**
		 * Consume question message for get competition info from competition_service.
		 */
		$response = $this->brokerService->consumePureMessage([config('broker.queues.question')], 10);
		$this->assertNotEmpty($response);
		$playerMessage = json_decode(json_encode($response[0]), true);
		$payload = json_decode($playerMessage, true);
		$this->assertEquals(config('broker.services.team_name'), $payload['headers']['source']);
		$this->assertEquals(config('broker.services.competition_name'), $payload['headers']['destination']);
		$this->assertEquals(TrophyProjectorListener::BROKER_EVENT_KEY, $payload['headers']['key']);
		$this->assertEquals(
			sprintf('%s#%s#%s',
				$message->getBody()->getIdentifiers()['competition'],
				$message->getBody()->getIdentifiers()['tournament'],
				$message->getBody()->getIdentifiers()['team']
			)
			, $payload['headers']['id']);
		$this->assertEquals(config('broker.services.tournament_name'), $payload['body']['entity']);
		$this->assertEquals($message->getBody()->getIdentifiers()['tournament'], $payload['body']['id']);
		/**
		 * Produce answer message from player service for update player info in transfer model.
		 */
		$answerMessage = (new CommandQueryMessage())
			->setHeaders(
				(new Headers())
					->setKey(TrophyProjectorListener::BROKER_EVENT_KEY)
					->setId($message->getBody()->getIdentifiers()['tournament'])
					->setDestination(config('broker.services.team_name'))
					->setSource(config('broker.services.competition_name'))
					->setDate(Carbon::now()->toDateTimeString())
			)->setBody([]);
		/**
		 * Handle answer message from competition service for update trophy info in trophy model.
		 */
		app(TrophyUpdateInfo::class)->handle($answerMessage);
		/**
		 * Read from DB
		 * @var Trophy $trophy
		 */
		$trophies = $this->trophyRepository->findAll();
		foreach ($trophies as $trophy) {
			$this->assertNull($trophy->getCompetitionName());
			$this->assertEquals("0", $trophy->getTournamentSeason());
		}
	}

	protected function tearDown(): void
	{
		$this->trophyRepository->drop();
		$this->teamRepository->drop();
		$this->brokerMessageCacheService->flush();
	}
}