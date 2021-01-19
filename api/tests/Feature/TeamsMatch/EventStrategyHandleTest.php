<?php


namespace Tests\Feature\TeamsMatch;


use App\Exceptions\Projection\ProjectionException;
use App\Models\ReadModels\TeamsMatch;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TeamsMatchRepository;
use App\Services\EventStrategy\MatchFinished;
use App\Services\EventStrategy\MatchStatusChanged;
use App\Services\EventStrategy\MatchWasCreated;
use App\ValueObjects\Broker\Mediator\Message;
use Carbon\Carbon;
use Faker\Factory;
use TestCase;
use Tests\Traits\TeamRepositoryTestTrait;
use Tests\Traits\TeamsMatchRepositoryTestTrait;


/**
 * Class EventStrategyHandleTest
 * @package Tests\Feature\TeamsMatch
 */
class EventStrategyHandleTest extends TestCase
{
	use TeamRepositoryTestTrait,
		TeamsMatchRepositoryTestTrait;

	private TeamRepository $teamRepository;
	private \Faker\Generator $faker;
	private TeamsMatchRepository $teamsMatchRepository;

	protected function setUp(): void
	{
		$this->createApplication();
		$this->faker = Factory::create();
		$this->teamRepository = app(TeamRepository::class);
		$this->teamsMatchRepository = app(TeamsMatchRepository::class);
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
                "date": "%s"
            },
			"body":{
				"identifiers": {
					"match":"%s",
					"home":"%s",
					"away":"%s"
				 },
				"metadata": {
					"date": "2006-02-19Z",
					"time": "11:30:00Z"
				}
			}
		}',
			config('mediator-event.events.match_was_created'),
			Carbon::now()->toDateTimeString(),
			$this->faker->uuid,
			$fakeHomeId,
			$fakeAwayId);
		/**
		 * @var Message $message
		 */
		$message = app('Serializer')->deserialize($message, Message::class, 'json');
		app(MatchWasCreated::class)->handle($message->getBody());
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
	}

	public function testMatchWasCreatedHandleWhenIdentifierIsNull()
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
					"match":"",
					"home":"",
					"away":""
				 },
				"metadata": {}
			}
		}',
			config('mediator-event.events.match_was_created'),
			Carbon::now()->toDateTimeString());
		/**
		 * @var Message $message
		 */
		$message = app('Serializer')->deserialize($message, Message::class, 'json');
		app(MatchWasCreated::class)->handle($message->getBody());
	}

	public function testMatchWasCreatedHandleWhenMetadataIsNull()
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
					"match":"%s",
					"home":"%s",
					"away":"%s"
				 },
				"metadata": {
					"date": "",
					"time": ""
				}
			}
		}',
			config('mediator-event.events.match_was_created'),
			Carbon::now()->toDateTimeString(),
			$this->faker->uuid,
			$this->faker->uuid,
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = app('Serializer')->deserialize($message, Message::class, 'json');
		app(MatchWasCreated::class)->handle($message->getBody());
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
                "date": "%s"
            },
			"body":{
				"identifiers": {
					"match":"%s",
					"home":"%s",
					"away":"%s"
				 },
				"metadata": {
					"date": "2006-02-19Z",
					"time": "11:30:00Z"
				}
			}
		}',
			config('mediator-event.events.match_was_created'),
			Carbon::now()->toDateTimeString(),
			$this->faker->uuid,
			$fakeHomeId,
			$fakeAwayId);
		/**
		 * @var Message $message
		 */
		$message = app('Serializer')->deserialize($message, Message::class, 'json');
		app(MatchWasCreated::class)->handle($message->getBody());
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
                "date": "%s"
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
			Carbon::now()->toDateTimeString(),
			$fakeMatchIdForUpcoming,
			$teamId);
		/**
		 * @var Message $message
		 */
		$message = app('Serializer')->deserialize($message, Message::class, 'json');
		app(MatchFinished::class)->handle($message->getBody());
		$teamsMatch = $this->teamsMatchRepository->findTeamsMatchByMatchId($fakeMatchIdForUpcoming);
		$this->assertCount(2, $teamsMatch);
		foreach ($teamsMatch as $item) {
			$this->assertInstanceOf(TeamsMatch::class, $item);
			$this->assertEquals(TeamsMatch::STATUS_FINISHED, $item->getStatus());
			$this->assertNotEmpty($item->getResult());
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
                "date": "%s"
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
			Carbon::now()->toDateTimeString(),
			$fakeMatchIdForUpcoming, null);
		/**
		 * @var Message $message
		 */
		$message = app('Serializer')->deserialize($message, Message::class, 'json');
		app(MatchFinished::class)->handle($message->getBody());
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
                "date": "%s"
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
			Carbon::now()->toDateTimeString());
		/**
		 * @var Message $message
		 */
		$message = app('Serializer')->deserialize($message, Message::class, 'json');
		app(MatchFinished::class)->handle($message->getBody());
	}

	public function testMatchFinishedHandleWithNullMetadata()
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
					"match":"%s",
					"winner":"%s"
				 },
				"metadata": {
					"scores": []
				}
			}
		}',
			config('mediator-event.events.match_finished'),
			Carbon::now()->toDateTimeString(),
			$this->faker->uuid,
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = app('Serializer')->deserialize($message, Message::class, 'json');
		app(MatchFinished::class)->handle($message->getBody());
	}

	public function testMatchFinishedHandleWhenMatchItemNotExist()
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
			Carbon::now()->toDateTimeString(),
			$this->faker->uuid,
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = app('Serializer')->deserialize($message, Message::class, 'json');
		app(MatchFinished::class)->handle($message->getBody());
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
                "date": "%s"
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
			Carbon::now()->toDateTimeString(),
			$fakeMatchIdForUpcoming);
		/**
		 * @var Message $message
		 */
		$message = app('Serializer')->deserialize($message, Message::class, 'json');
		app(MatchStatusChanged::class)->handle($message->getBody());
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
                "date": "%s"
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
			Carbon::now()->toDateTimeString(),
			$fakeMatchIdForUpcoming);
		/**
		 * @var Message $message
		 */
		$message = app('Serializer')->deserialize($message, Message::class, 'json');
		app(MatchStatusChanged::class)->handle($message->getBody());
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
                "date": "%s"
            },
			"body":{
				"identifiers": {
					"match":""
				 },
				"metadata": {}
			}
		}',
			config('mediator-event.events.match_status_changed'),
			Carbon::now()->toDateTimeString());
		/**
		 * @var Message $message
		 */
		$message = app('Serializer')->deserialize($message, Message::class, 'json');
		app(MatchStatusChanged::class)->handle($message->getBody());
	}

	public function testMatchStatusChangedHandleWhenMetaDataIsNull()
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
					"match":"%s"
				 },
				"metadata": {
					"status": ""
				}
			}
		}',
			config('mediator-event.events.match_status_changed'),
			Carbon::now()->toDateTimeString(),
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = app('Serializer')->deserialize($message, Message::class, 'json');
		app(MatchStatusChanged::class)->handle($message->getBody());
	}

	protected function tearDown(): void
	{
		$this->teamRepository->drop();
		$this->teamsMatchRepository->drop();
	}
}