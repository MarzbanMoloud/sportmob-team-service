<?php


namespace Tests\Feature\TeamsMatch;


use App\Exceptions\Projection\ProjectionException;
use App\Listeners\Projection\MatchWasCreatedProjectorListener;
use App\Models\ReadModels\TeamsMatch;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TeamsMatchRepository;
use App\Services\BrokerCommandStrategy\MatchWasCreatedUpdatedInfo;
use App\Services\Cache\TeamsMatchCacheService;
use App\Services\EventStrategy\MatchFinished;
use App\Services\EventStrategy\MatchStatusChanged;
use App\Services\EventStrategy\MatchWasCreated;
use App\ValueObjects\Broker\CommandQuery\Headers;
use App\ValueObjects\Broker\CommandQuery\Message as CommandQueryMessage;
use App\ValueObjects\Broker\Mediator\Message;
use Carbon\Carbon;
use Faker\Factory;
use Symfony\Component\Serializer\SerializerInterface;
use TestCase;
use Tests\Traits\AmazonBrokerTrait;
use Tests\Traits\TeamRepositoryTestTrait;
use Tests\Traits\TeamsMatchRepositoryTestTrait;


/**
 * Class EventStrategyHandleTest
 * @package Tests\Feature\TeamsMatch
 */
class EventStrategyHandleTest extends TestCase
{
	use TeamRepositoryTestTrait,
		TeamsMatchRepositoryTestTrait,
		AmazonBrokerTrait;

	private TeamRepository $teamRepository;
	private \Faker\Generator $faker;
	private TeamsMatchRepository $teamsMatchRepository;
	private TeamsMatchCacheService $teamsMatchCacheService;
	private SerializerInterface $serializer;

	protected function setUp(): void
	{
		$this->createApplication();
		$this->faker = Factory::create();
		$this->serializer = app(SerializerInterface::class);
		$this->teamRepository = app(TeamRepository::class);
		$this->teamsMatchCacheService = app(TeamsMatchCacheService::class);
		$this->teamsMatchRepository = app(TeamsMatchRepository::class);
		$this->setupAWSBroker();
		$this->createTeamTable();
		$this->createTeamsMatchTable();
	}

	public function testMatchWasCreatedHandle()
	{
		/**
		 * Create fake team model for Home.
		 */
		$fakeHomeId = $this->faker->uuid;
		$fakeHomeTeamModel = $this->createTeamModel();
		$fakeHomeTeamModel->setId($fakeHomeId);
		$this->teamRepository->persist($fakeHomeTeamModel);
		/**
		 * Create fake team model for Away.
		 */
		$fakeAwayId = $this->faker->uuid;
		$fakeAwayTeamModel = $this->createTeamModel();
		$fakeAwayTeamModel->setId($fakeAwayId);
		$this->teamRepository->persist($fakeAwayTeamModel);

		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s",
                "id": "1"
            },
			"body":{
				"identifiers": {
					"match":"%s",
					"home":"%s",
					"away":"%s",
					"competition": "%s",
					"tournament": "3dr99391m0",
					"stage": "82z9j09xxk"
				 },
				"metadata": {
					"coverage": "high",
					"date": "2006-02-19Z",
					"time": "11:30:00Z",
					"stage": "Champions League Grp.A"
				}
			}
		}',
			config('mediator-event.events.match_was_created'),
			Carbon::now()->format('c'),
			$this->faker->uuid,
			$fakeHomeId,
			$fakeAwayId,
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		app(MatchWasCreated::class)->handle($message);
		/**
		 * Read from DB.
		 */
		$teamsMatch = $this->teamsMatchRepository->findAll();
		$this->assertCount(2, $teamsMatch);
		foreach ($teamsMatch as $item) {
			/**
			 * @var TeamsMatch $item
			 */
			$this->assertInstanceOf(TeamsMatch::class, $item);
			$this->assertEquals($message->getBody()->getIdentifiers()['match'], $item->getMatchId());
			$this->assertNotNull($item->getStageId());
			$this->assertNotNull($item->getStageName());
			$this->assertNotNull($item->getTournamentId());
			$this->assertNotNull($item->getTeamId());
			$this->assertNotNull($item->getTeamName());
			$this->assertNotNull($item->getOpponentId());
			$this->assertNotNull($item->getOpponentName());
			$this->assertEquals(TeamsMatch::STATUS_UPCOMING, $item->getStatus());
			$this->assertNotNull($item->getSortKey());
			$this->assertIsBool($item->isHome());
			$this->assertEmpty($item->getResult());
			$this->assertNull($item->getEvaluation());
			$this->assertEquals('high', $item->getCoverage());
		}
		/**
		 * Consume question message for get competition name of CompetitionService
		 */
		$response = $this->brokerService->consumePureMessage([config('broker.queues.question')], 10);
		$this->assertNotEmpty($response);
		$response = json_decode(json_encode($response[0]), true);
		$payload = json_decode($response, true);
		$this->assertEquals(config('broker.services.team_name'), $payload['headers']['source']);
		$this->assertEquals(config('broker.services.competition_name'), $payload['headers']['destination']);
		$this->assertEquals(MatchWasCreatedProjectorListener::BROKER_EVENT_KEY, $payload['headers']['key']);
		$this->assertEquals(
			sprintf('%s#%s#%s',
				$message->getBody()->getIdentifiers()['match'],
				$message->getBody()->getIdentifiers()['home'],
				$message->getBody()->getIdentifiers()['away']
			), $payload['headers']['id']);
		$this->assertNotNull($payload['headers']['date']);
		$this->assertEquals($message->getBody()->getIdentifiers()['competition'], $payload['body']['id']);
		$this->assertEquals(config('broker.services.competition_name'), $payload['body']['entity']);
		/**
		 * Produce answer message from CompetitionService for competition name.
		 */
		$answerMessage = (new CommandQueryMessage())
			->setHeaders(
				(new Headers())
					->setEventId('1')
					->setKey(MatchWasCreatedProjectorListener::BROKER_EVENT_KEY)
					->setId($payload['headers']['id'])
					->setDestination(config('broker.services.team_name'))
					->setSource(config('broker.services.competition_name'))
					->setDate(Carbon::now()->format('c'))
			)->setBody([
				'entity' => config('broker.services.competition_name'),
				'id' => $payload['body']['id'],
				'name' => 'Premier League',
			]);
		/**
		 * Handle answer message from player service for update player info in transfer model.
		 */
		app(MatchWasCreatedUpdatedInfo::class)->handle($answerMessage);
		$teamsMatchItem = $this->teamsMatchRepository->find([
			'matchId' => $message->getBody()->getIdentifiers()['match'],
			'teamId' => $message->getBody()->getIdentifiers()['home']
		]);
		$this->assertEquals('Premier League', $teamsMatchItem->getCompetitionName());
		$teamsMatchItem = $this->teamsMatchRepository->find([
			'matchId' => $message->getBody()->getIdentifiers()['match'],
			'teamId' => $message->getBody()->getIdentifiers()['away']
		]);
		$this->assertEquals('Premier League', $teamsMatchItem->getCompetitionName());

		/**
		 * Read from Cache
		 */
		$response = app('cache')->get($this->teamsMatchCacheService->getTeamsMatchByTeamIdKey($fakeHomeId, TeamsMatch::STATUS_UPCOMING));
		$this->assertInstanceOf(TeamsMatch::class, $response[0]);
		$response = app('cache')->get($this->teamsMatchCacheService->getTeamsMatchByTeamIdKey($fakeAwayId, TeamsMatch::STATUS_UPCOMING));
		$this->assertInstanceOf(TeamsMatch::class, $response[0]);
	}

	public function testMatchWasCreatedHandleWhenCoverageIsNull()
	{
		/**
		 * Create fake team model for Home.
		 */
		$fakeHomeId = $this->faker->uuid;
		$fakeHomeTeamModel = $this->createTeamModel();
		$fakeHomeTeamModel->setId($fakeHomeId);
		$this->teamRepository->persist($fakeHomeTeamModel);
		/**
		 * Create fake team model for Away.
		 */
		$fakeAwayId = $this->faker->uuid;
		$fakeAwayTeamModel = $this->createTeamModel();
		$fakeAwayTeamModel->setId($fakeAwayId);
		$this->teamRepository->persist($fakeAwayTeamModel);

		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s",
                "id": "1"
            },
			"body":{
				"identifiers": {
					"match":"%s",
					"home":"%s",
					"away":"%s",
					"competition": "%s",
					"tournament": "3dr99391m0",
					"stage": "82z9j09xxk"
				},
				"metadata": {
					"coverage": "",
					"date": "2006-02-19Z",
					"time": "11:30:00Z",
					"stage": "Champions League Grp.A"
				}
			}
		}',
			config('mediator-event.events.match_was_created'),
			Carbon::now()->format('c'),
			$this->faker->uuid,
			$fakeHomeId,
			$fakeAwayId,
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		app(MatchWasCreated::class)->handle($message);
		/**
		 * Read from DB.
		 */
		$teamsMatch = $this->teamsMatchRepository->findAll();
		$this->assertCount(2, $teamsMatch);
		foreach ($teamsMatch as $item) {
			/**
			 * @var TeamsMatch $item
			 */
			$this->assertInstanceOf(TeamsMatch::class, $item);
			$this->assertEquals($message->getBody()->getIdentifiers()['match'], $item->getMatchId());
			$this->assertNotNull($item->getStageId());
			$this->assertNotNull($item->getStageName());
			$this->assertNotNull($item->getTournamentId());
			$this->assertNotNull($item->getTeamId());
			$this->assertNotNull($item->getTeamName());
			$this->assertNotNull($item->getOpponentId());
			$this->assertNotNull($item->getOpponentName());
			$this->assertEquals(TeamsMatch::STATUS_UPCOMING, $item->getStatus());
			$this->assertNotNull($item->getSortKey());
			$this->assertIsBool($item->isHome());
			$this->assertEmpty($item->getResult());
			$this->assertNull($item->getEvaluation());
			$this->assertEquals('low', $item->getCoverage());
		}
		/**
		 * Consume question message for get competition name of CompetitionService
		 */
		$response = $this->brokerService->consumePureMessage([config('broker.queues.question')], 10);
		$this->assertNotEmpty($response);
		$response = json_decode(json_encode($response[0]), true);
		$payload = json_decode($response, true);
		$this->assertEquals(config('broker.services.team_name'), $payload['headers']['source']);
		$this->assertEquals(config('broker.services.competition_name'), $payload['headers']['destination']);
		$this->assertEquals(MatchWasCreatedProjectorListener::BROKER_EVENT_KEY, $payload['headers']['key']);
		$this->assertEquals(
			sprintf('%s#%s#%s',
				$message->getBody()->getIdentifiers()['match'],
				$message->getBody()->getIdentifiers()['home'],
				$message->getBody()->getIdentifiers()['away']
			), $payload['headers']['id']);
		$this->assertNotNull($payload['headers']['date']);
		$this->assertEquals($message->getBody()->getIdentifiers()['competition'], $payload['body']['id']);
		$this->assertEquals(config('broker.services.competition_name'), $payload['body']['entity']);
		/**
		 * Produce answer message from CompetitionService for competition name.
		 */
		$answerMessage = (new CommandQueryMessage())
			->setHeaders(
				(new Headers())
					->setEventId('1')
					->setKey(MatchWasCreatedProjectorListener::BROKER_EVENT_KEY)
					->setId($payload['headers']['id'])
					->setDestination(config('broker.services.team_name'))
					->setSource(config('broker.services.competition_name'))
					->setDate(Carbon::now()->format('c'))
			)->setBody([
				'entity' => config('broker.services.competition_name'),
				'id' => $payload['body']['id'],
				'name' => 'Premier League',
			]);
		/**
		 * Handle answer message from player service for update player info in transfer model.
		 */
		app(MatchWasCreatedUpdatedInfo::class)->handle($answerMessage);
		$teamsMatchItem = $this->teamsMatchRepository->find([
			'matchId' => $message->getBody()->getIdentifiers()['match'],
			'teamId' => $message->getBody()->getIdentifiers()['home']
		]);
		$this->assertEquals('Premier League', $teamsMatchItem->getCompetitionName());
		$teamsMatchItem = $this->teamsMatchRepository->find([
			'matchId' => $message->getBody()->getIdentifiers()['match'],
			'teamId' => $message->getBody()->getIdentifiers()['away']
		]);
		$this->assertEquals('Premier League', $teamsMatchItem->getCompetitionName());
	}

	public function testMatchWasCreatedHandleWhenCompetitionNameIsNull()
	{
		/**
		 * Create fake team model for Home.
		 */
		$fakeHomeId = $this->faker->uuid;
		$fakeHomeTeamModel = $this->createTeamModel();
		$fakeHomeTeamModel->setId($fakeHomeId);
		$this->teamRepository->persist($fakeHomeTeamModel);
		/**
		 * Create fake team model for Away.
		 */
		$fakeAwayId = $this->faker->uuid;
		$fakeAwayTeamModel = $this->createTeamModel();
		$fakeAwayTeamModel->setId($fakeAwayId);
		$this->teamRepository->persist($fakeAwayTeamModel);

		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s",
                "id": "1"
            },
			"body":{
				"identifiers": {
					"match":"%s",
					"home":"%s",
					"away":"%s",
					"competition": "%s",
					"tournament": "3dr99391m0",
					"stage": "82z9j09xxk"
				 },
				"metadata": {
					"coverage": "high",
					"date": "2006-02-19Z",
					"time": "11:30:00Z",
					"stage": "Champions League Grp.A"
				}
			}
		}',
			config('mediator-event.events.match_was_created'),
			Carbon::now()->format('c'),
			$this->faker->uuid,
			$fakeHomeId,
			$fakeAwayId,
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		app(MatchWasCreated::class)->handle($message);
		/**
		 * Read from DB.
		 */
		$teamsMatch = $this->teamsMatchRepository->findAll();
		$this->assertCount(2, $teamsMatch);
		foreach ($teamsMatch as $item) {
			/**
			 * @var TeamsMatch $item
			 */
			$this->assertInstanceOf(TeamsMatch::class, $item);
			$this->assertEquals($message->getBody()->getIdentifiers()['match'], $item->getMatchId());
			$this->assertNotNull($item->getStageId());
			$this->assertNotNull($item->getStageName());
			$this->assertNotNull($item->getTournamentId());
			$this->assertNotNull($item->getTeamId());
			$this->assertNotNull($item->getTeamName());
			$this->assertNotNull($item->getOpponentId());
			$this->assertNotNull($item->getOpponentName());
			$this->assertEquals(TeamsMatch::STATUS_UPCOMING, $item->getStatus());
			$this->assertNotNull($item->getSortKey());
			$this->assertIsBool($item->isHome());
			$this->assertEmpty($item->getResult());
			$this->assertNull($item->getEvaluation());
		}
		/**
		 * Consume question message for get competition name of CompetitionService
		 */
		$response = $this->brokerService->consumePureMessage([config('broker.queues.question')], 10);
		$this->assertNotEmpty($response);
		$response = json_decode(json_encode($response[0]), true);
		$payload = json_decode($response, true);
		$this->assertEquals(config('broker.services.team_name'), $payload['headers']['source']);
		$this->assertEquals(config('broker.services.competition_name'), $payload['headers']['destination']);
		$this->assertEquals(MatchWasCreatedProjectorListener::BROKER_EVENT_KEY, $payload['headers']['key']);
		$this->assertEquals(
			sprintf('%s#%s#%s',
				$message->getBody()->getIdentifiers()['match'],
				$message->getBody()->getIdentifiers()['home'],
				$message->getBody()->getIdentifiers()['away']
			), $payload['headers']['id']);
		$this->assertNotNull($payload['headers']['date']);
		$this->assertEquals($message->getBody()->getIdentifiers()['competition'], $payload['body']['id']);
		$this->assertEquals(config('broker.services.competition_name'), $payload['body']['entity']);
		/**
		 * Produce answer message from CompetitionService for competition name.
		 */
		$answerMessage = (new CommandQueryMessage())
			->setHeaders(
				(new Headers())
					->setEventId('1')
					->setKey(MatchWasCreatedProjectorListener::BROKER_EVENT_KEY)
					->setId($payload['headers']['id'])
					->setDestination(config('broker.services.team_name'))
					->setSource(config('broker.services.competition_name'))
					->setDate(Carbon::now()->format('c'))
			)->setBody([]);
		/**
		 * Handle answer message from player service for update player info in transfer model.
		 */
		app(MatchWasCreatedUpdatedInfo::class)->handle($answerMessage);
		$teamsMatchItem = $this->teamsMatchRepository->find([
			'matchId' => $message->getBody()->getIdentifiers()['match'],
			'teamId' => $message->getBody()->getIdentifiers()['home']
		]);
		$this->assertNull($teamsMatchItem->getCompetitionName());
		$teamsMatchItem = $this->teamsMatchRepository->find([
			'matchId' => $message->getBody()->getIdentifiers()['match'],
			'teamId' => $message->getBody()->getIdentifiers()['away']
		]);
		$this->assertNull($teamsMatchItem->getCompetitionName());
	}

	public function testMatchWasCreatedHandleWhenIdentifierIsNull()
	{
		$this->expectException(ProjectionException::class);
		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s",
                "id": "1"
            },
			"body":{
				"identifiers": {
					"match":"",
					"home":[],
					"away":[],
					"competition":""
				 },
				"metadata": {}
			}
		}',
			config('mediator-event.events.match_was_created'),
			Carbon::now()->format('c'));
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		app(MatchWasCreated::class)->handle($message);
	}

	public function testMatchWasCreatedHandleWhenMetadataIsNull()
	{
		$this->expectException(ProjectionException::class);
		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s",
                "id": "1"
            },
			"body":{
				"identifiers": {
					"match":"%s",
					"home":"%s",
					"away":"%s",
					"competition":"%s"
				 },
				"metadata": {
					"coverage": "",
					"date": [],
					"time": []
				}
			}
		}',
			config('mediator-event.events.match_was_created'),
			Carbon::now()->format('c'),
			$this->faker->uuid,
			$this->faker->uuid,
			$this->faker->uuid,
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		app(MatchWasCreated::class)->handle($message);
	}

	public function testMatchWasCreatedHandleWhenTeamItemNotExist()
	{
		$this->expectException(ProjectionException::class);

		$fakeHomeId = $this->faker->uuid;
		$fakeAwayId = $this->faker->uuid;

		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s",
                "id": "1"
            },
			"body":{
				"identifiers": {
					"match":"%s",
					"home":"%s",
					"away":"%s",
					"competition":"%s"
				 },
				"metadata": {
					"coverage": "high",
					"date": "2006-02-19Z",
					"time": "11:30:00Z"
				}
			}
		}',
			config('mediator-event.events.match_was_created'),
			Carbon::now()->format('c'),
			$this->faker->uuid,
			$fakeHomeId,
			$fakeAwayId,
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		app(MatchWasCreated::class)->handle($message);
	}

	public function testMatchFinishedHandle()
	{
		$teamId = $this->faker->uuid;
		$opponentId = $this->faker->uuid;
		$teamName = $this->faker->name;
		$opponentName = $this->faker->name;
		/**
		 * Upcoming status.
		 */
		$fakeMatchIdForUpcoming = $this->faker->uuid;

		$this->createTeamsMatchModel(
			$teamId,
			$opponentId,
			$teamName,
			$opponentName,
			$fakeMatchIdForUpcoming,
			true
		);
		$this->createTeamsMatchModel(
			$opponentId,
			$teamId,
			$opponentName,
			$teamName,
			$fakeMatchIdForUpcoming,
			false
		);

		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s",
                "id": "1"
            },
			"body":{
				"identifiers": {
					"match":"%s",
					"winner":"%s"
				 },
				"metadata": {
					"scores": [
						{
						   "type":"firstHalf",
						   "home":1,
						   "away":1
						},
						{
						   "type":"secondHalf",
						   "home":2,
						   "away":2
						},
						{
						   "type":"total",
						   "home":2,
						   "away":2
						},
						{
						   "type":"penalty",
						   "home":1,
						   "away":1
						}
        			]
				}
			}
		}',
			config('mediator-event.events.match_finished'),
			Carbon::now()->format('c'),
			$fakeMatchIdForUpcoming,
			$teamId);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		app(MatchFinished::class)->handle($message);
		$teamsMatch = $this->teamsMatchRepository->findTeamsMatchByMatchId($fakeMatchIdForUpcoming);
		$this->assertCount(2, $teamsMatch);
		foreach ($teamsMatch as $item) {
			$this->assertInstanceOf(TeamsMatch::class, $item);
			$this->assertEquals(TeamsMatch::STATUS_FINISHED, $item->getStatus());
			$this->assertNotEmpty($item->getResult());
			$this->assertCount(2, $item->getResult());
			$this->assertEquals([
				"total" => [
					"home" => 2,
					"away" => 2
				],
				"penalty" => [
					"home" => 1,
					"away" => 1
				]
			], $item->getResult());

			$this->assertContains($item->getEvaluation(), [TeamsMatch::EVALUATION_WIN, TeamsMatch::EVALUATION_LOSS]);
		}
	}

	public function testMatchFinishedHandleWhenWinnerIsNull()
	{
		$teamId = $this->faker->uuid;
		$opponentId = $this->faker->uuid;
		$teamName = $this->faker->name;
		$opponentName = $this->faker->name;
		/**
		 * Upcoming status.
		 */
		$fakeMatchIdForUpcoming = $this->faker->uuid;

		$this->createTeamsMatchModel(
			$teamId,
			$opponentId,
			$teamName,
			$opponentName,
			$fakeMatchIdForUpcoming,
			true
		);
		$this->createTeamsMatchModel(
			$opponentId,
			$teamId,
			$opponentName,
			$teamName,
			$fakeMatchIdForUpcoming,
			false
		);

		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s",
                "id": "1"
            },
			"body":{
				"identifiers": {
					"match":"%s",
					"winner":"%s"
				 },
				"metadata": {
					"scores": [
						{
						   "type":"firstHalf",
						   "home":1,
						   "away":1
						},
						{
						   "type":"secondHalf",
						   "home":2,
						   "away":2
						},
						{
						   "type":"total",
						   "home":2,
						   "away":2
						}
        			]
				}
			}
		}',
			config('mediator-event.events.match_finished'),
			Carbon::now()->format('c'),
			$fakeMatchIdForUpcoming, null);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		app(MatchFinished::class)->handle($message);
		$teamsMatch = $this->teamsMatchRepository->findTeamsMatchByMatchId($fakeMatchIdForUpcoming);
		$this->assertCount(2, $teamsMatch);
		foreach ($teamsMatch as $item) {
			$this->assertInstanceOf(TeamsMatch::class, $item);
			$this->assertEquals(TeamsMatch::STATUS_FINISHED, $item->getStatus());
			$this->assertNotEmpty($item->getResult());
			$this->assertEquals(TeamsMatch::EVALUATION_DRAW, $item->getEvaluation());
		}
	}

	public function testMatchFinishedHandleWithNullIdentifiers()
	{
		$this->expectException(ProjectionException::class);
		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s",
                "id": "1"
            },
			"body":{
				"identifiers": {
					"match":"",
					"winner":""
				 },
				"metadata": {}
			}
		}',
			config('mediator-event.events.match_finished'),
			Carbon::now()->format('c'));
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		app(MatchFinished::class)->handle($message);
	}

	public function testMatchFinishedHandleWithNullMetadata()
	{
		$this->expectException(ProjectionException::class);
		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s",
                "id": "1"
            },
			"body":{
				"identifiers": {
					"match":"%s",
					"winner":"%s"
				 },
				"metadata": {
					"scores": []
				}
			}
		}',
			config('mediator-event.events.match_finished'),
			Carbon::now()->format('c'),
			$this->faker->uuid,
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		app(MatchFinished::class)->handle($message);
	}

	public function testMatchFinishedHandleWhenMatchItemNotExist()
	{
		$this->expectException(ProjectionException::class);
		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s",
                "id": "1"
            },
			"body":{
				"identifiers": {
					"match":"%s",
					"winner":"%s"
				 },
				"metadata": {
					"scores": [
						{
						   "type":"firstHalf",
						   "home":1,
						   "away":1
						},
						{
						   "type":"secondHalf",
						   "home":2,
						   "away":2
						},
						{
						   "type":"total",
						   "home":2,
						   "away":2
						}
        			]
				}
			}
		}',
			config('mediator-event.events.match_finished'),
			Carbon::now()->format('c'),
			$this->faker->uuid,
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		app(MatchFinished::class)->handle($message);
	}

	public function testMatchStatusChangedHandle()
	{
		$teamId = $this->faker->uuid;
		$opponentId = $this->faker->uuid;
		$teamName = $this->faker->name;
		$opponentName = $this->faker->name;
		/**
		 * Upcoming status.
		 */
		$fakeMatchIdForUpcoming = $this->faker->uuid;

		$this->createTeamsMatchModel(
			$teamId,
			$opponentId,
			$teamName,
			$opponentName,
			$fakeMatchIdForUpcoming,
			true
		);
		$this->createTeamsMatchModel(
			$opponentId,
			$teamId,
			$opponentName,
			$teamName,
			$fakeMatchIdForUpcoming,
			false
		);

		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s",
                "id": "1"
            },
			"body":{
				"identifiers": {
					"match":"%s"
				 },
				"metadata": {
					"status": "gameEnded"
				}
			}
		}',
			config('mediator-event.events.match_status_changed'),
			Carbon::now()->format('c'),
			$fakeMatchIdForUpcoming);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		app(MatchStatusChanged::class)->handle($message);
		$teamsMatch = $this->teamsMatchRepository->findTeamsMatchByMatchId($fakeMatchIdForUpcoming);
		$this->assertCount(2, $teamsMatch);
		foreach ($teamsMatch as $item) {
			$this->assertInstanceOf(TeamsMatch::class, $item);
			$this->assertEquals(TeamsMatch::STATUS_FINISHED, $item->getStatus());
		}
	}

	public function testMatchStatusChangedHandleWithUnknownStatus()
	{
		$teamId = $this->faker->uuid;
		$opponentId = $this->faker->uuid;
		$teamName = $this->faker->name;
		$opponentName = $this->faker->name;
		/**
		 * Upcoming status.
		 */
		$fakeMatchIdForUpcoming = $this->faker->uuid;

		$this->createTeamsMatchModel(
			$teamId,
			$opponentId,
			$teamName,
			$opponentName,
			$fakeMatchIdForUpcoming,
			true
		);
		$this->createTeamsMatchModel(
			$opponentId,
			$teamId,
			$opponentName,
			$teamName,
			$fakeMatchIdForUpcoming,
			false
		);

		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s",
                "id": "1"
            },
			"body":{
				"identifiers": {
					"match":"%s"
				 },
				"metadata": {
					"status": "penalty"
				}
			}
		}',
			config('mediator-event.events.match_status_changed'),
			Carbon::now()->format('c'),
			$fakeMatchIdForUpcoming);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		app(MatchStatusChanged::class)->handle($message);
		$teamsMatch = $this->teamsMatchRepository->findTeamsMatchByMatchId($fakeMatchIdForUpcoming);
		$this->assertCount(2, $teamsMatch);
		foreach ($teamsMatch as $item) {
			$this->assertInstanceOf(TeamsMatch::class, $item);
			$this->assertEquals(TeamsMatch::STATUS_UNKNOWN, $item->getStatus());
		}
	}

	public function testMatchStatusChangedHandleWhenIdentifiersIsNull()
	{
		$this->expectException(ProjectionException::class);
		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s",
                "id": "1"
            },
			"body":{
				"identifiers": {
					"match":""
				 },
				"metadata": {}
			}
		}',
			config('mediator-event.events.match_status_changed'),
			Carbon::now()->format('c'));
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		app(MatchStatusChanged::class)->handle($message);
	}

	public function testMatchStatusChangedHandleWhenMetaDataIsNull()
	{
		$this->expectException(ProjectionException::class);
		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s",
                "id": "1"
            },
			"body":{
				"identifiers": {
					"match":"%s"
				 },
				"metadata": {
					"status": ""
				}
			}
		}',
			config('mediator-event.events.match_status_changed'),
			Carbon::now()->format('c'),
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		app(MatchStatusChanged::class)->handle($message);
	}

	protected function tearDown(): void
	{
		$this->teamRepository->drop();
		$this->teamsMatchRepository->drop();
	}
}