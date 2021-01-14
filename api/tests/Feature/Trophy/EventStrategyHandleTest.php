<?php


namespace Tests\Feature\Trophy;


use App\Exceptions\Projection\ProjectionException;
use App\Listeners\Projection\TrophyProjectorListener;
use App\Models\ReadModels\Trophy;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TrophyRepository;
use App\Services\BrokerCommandStrategy\TrophyUpdateInfo;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
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

	protected function setUp(): void
	{
		$this->createApplication();
		$this->faker = Factory::create();
		$this->serializer = app('Serializer');
		$this->trophyRepository = app(TrophyRepository::class);
		$this->teamRepository = app(TeamRepository::class);
		$this->brokerMessageCacheService = app(BrokerMessageCacheServiceInterface::class);
		$this->setupAWSBroker();
		$this->createTrophyTable();
		$this->createTeamTable();
	}

	public function testTeamBecameWinnerHandle()
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
			$this->assertNotNull($trophy->getBelongTo());
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
		$this->assertEquals($message->getBody()->getIdentifiers()['tournament'], $payload['headers']['id']);
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
			)->setBody([
				'entity' => config('broker.services.tournament_name'),
				'id' => $message->getBody()->getIdentifiers()['tournament'],
				'competitionName' => $this->faker->name,
				'season' => '2020-2021'
			]);
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
			$this->assertEquals($answerMessage->getBody()['competitionName'], $trophy->getCompetitionName());
			$this->assertEquals($answerMessage->getBody()['season'], $trophy->getTournamentSeason());
		}
		/**
		 * Check broker message cache for trophy info.
		 */
		$brokerMessageCache = $this->brokerMessageCacheService->getTournamentInfo($message->getBody()->getIdentifiers()['tournament']);
		$this->assertEquals($answerMessage->getBody()['competitionName'], $brokerMessageCache['competitionName']);
		$this->assertEquals($answerMessage->getBody()['season'], $brokerMessageCache['season']);
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
			$this->assertNotNull($trophy->getBelongTo());
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
		$this->assertEquals($message->getBody()->getIdentifiers()['tournament'], $payload['headers']['id']);
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