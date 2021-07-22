<?php


namespace Tests\Feature\Transfer;


use App\Exceptions\Projection\ProjectionException;
use App\Listeners\Projection\MembershipWasUpdatedProjectorListener;
use App\Models\ReadModels\Transfer;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TransferRepository;
use App\Services\BrokerCommandStrategy\MembershipWasUpdatedUpdateInfo;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
use App\Services\Cache\TransferCacheService;
use App\Services\EventStrategy\MembershipWasUpdated;
use App\ValueObjects\Broker\CommandQuery\Headers;
use App\ValueObjects\Broker\CommandQuery\Message as CommandQueryMessage;
use App\ValueObjects\Broker\Mediator\Message;
use Carbon\Carbon;
use Faker\Factory;
use Symfony\Component\Serializer\SerializerInterface;
use TestCase;
use Tests\Traits\AmazonBrokerTrait;
use Tests\Traits\TeamRepositoryTestTrait;
use Tests\Traits\TransferRepositoryTestTrait;


/**
 * Class EventStrategyHandleTest
 * @package Tests\Feature\Transfer
 */
class EventStrategyHandleTest extends TestCase
{
	use TransferRepositoryTestTrait,
		TeamRepositoryTestTrait,
		AmazonBrokerTrait;

	private TransferRepository $transferRepository;
	private TeamRepository $teamRepository;
	private \Faker\Generator $faker;
	private SerializerInterface $serializer;
	private BrokerMessageCacheServiceInterface $brokerMessageCacheService;
	private TransferCacheService $transferCacheService;

	protected function setUp(): void
	{
		$this->createApplication();
		$this->faker = Factory::create();
		$this->serializer = app(SerializerInterface::class);
		$this->transferRepository = app(TransferRepository::class);
		$this->transferCacheService = app(TransferCacheService::class);
		$this->teamRepository = app(TeamRepository::class);
		$this->brokerMessageCacheService = app(BrokerMessageCacheServiceInterface::class);
		$this->setupAWSBroker();
		$this->createTeamTable();
		$this->createTransferTable();
	}

	public function testMembershipWasUpdatedHandleWhenActiveIsTrue()
	{
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
					"person": "%s"
				 },
				"metadata": {
					"type": "player",
					"membership": [
						{
							"id": "0010",
							"teamId": "00020",
							"teamType": "club",
							"dateFrom": null,
							"dateTo": null,
							"onLoanFrom": null
						},
						{
							"id": "0020",
							"teamId": "00030",
							"teamType": "club",
							"dateFrom": "2021-02-10",
							"dateTo": "2022-02-10",
							"onLoanFrom": null
						},
						{
							"id": "0030",
							"teamId": "00040",
							"teamType": "club",
							"dateFrom": "2022-05-10",
							"dateTo": "2022-12-10",
							"onLoanFrom": "000010"
						},
						{
							"id": "0040",
							"teamId": "00050",
							"teamType": "national",
							"dateFrom": null,
							"dateTo": null,
							"onLoanFrom": null
						}
					]
				}
			}
		}',
		config('mediator-event.events.membership_was_updated'),
		Carbon::now()->format('c'),
		$this->faker->uuid);

		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');

		/** Persist team items. */
		foreach ([00020, 00030, 00040, 00050] as $teamId) {
			$fakeTeamToModel = $this->createTeamModel()
				->setId($teamId);
			$this->teamRepository->persist($fakeTeamToModel);
		}

		/** Handle event. */
		app(MembershipWasUpdated::class)->handle($message);

		/**
		 * Read from DB
		 * @var Transfer $transfer
		 */
		$transfers = $this->transferRepository->findByPersonId($message->getBody()->getIdentifiers()['person']);
		$this->assertNotEmpty($transfers);
		$this->assertCount(3, $transfers);

		/** Consume question message for get player info from player_service. */
		$response = $this->brokerService->consumePureMessage([config('broker.queues.question')], 10);
		$this->assertNotEmpty($response);
		$this->assertCount(3, $response);

		$playerMessage = json_decode(json_encode($response[0]), true);
		$payload = json_decode($playerMessage, true);
		$this->assertEquals(config('broker.services.team_name'), $payload['headers']['source']);
		$this->assertEquals(config('broker.services.player_name'), $payload['headers']['destination']);
		$this->assertEquals(MembershipWasUpdatedProjectorListener::BROKER_EVENT_KEY, $payload['headers']['key']);
		$this->assertNotNull($payload['headers']['id']);
		$this->assertEquals(config('broker.services.player_name'), $payload['body']['entity']);
		$this->assertEquals($message->getBody()->getIdentifiers()['person'], $payload['body']['id']);

		/** Produce answer message from player service for update player info in transfer model. */
		$answerMessage = (new CommandQueryMessage())
			->setHeaders(
				(new Headers())
					->setEventId('1')
					->setKey(MembershipWasUpdatedProjectorListener::BROKER_EVENT_KEY)
					->setId($payload['headers']['id'])
					->setDestination(config('broker.services.team_name'))
					->setSource(config('broker.services.player_name'))
					->setDate(Carbon::now()->format('c'))
			)->setBody([
				'entity' => config('broker.services.player_name'),
				'id' => $message->getBody()->getIdentifiers()['person'],
				'fullName' => $this->faker->name,
				'shortName' => $this->faker->name
			]);

		/** Handle answer message from player service for update player info in transfer model. */
		app(MembershipWasUpdatedUpdateInfo::class)->handle($answerMessage);

		/**
		 * Check player info is update in transfer model.
		 * @var Transfer $transfer
		 */
		$transfer = $this->transferRepository->findByPersonId($message->getBody()->getIdentifiers()['person']);
		$this->assertNotEmpty($transfer);
		$transfer = $transfer[0];
		$this->assertEquals($answerMessage->getBody()['fullName'], $transfer->getPersonName());

		/**
		 * Check broker message cache for player info.
		 */
		$brokerMessageCache = $this->brokerMessageCacheService->getPlayerInfo($message->getBody()->getIdentifiers()['person']);
		$this->assertEquals($answerMessage->getBody()['fullName'], $brokerMessageCache['fullName']);
		$this->assertEquals($answerMessage->getBody()['shortName'], $brokerMessageCache['shortName']);
	}

	public function testMembershipWasUpdatedHandleWhenPlayerCacheExist()
	{
		$fakePlayerId = $this->faker->uuid;

		$this->brokerMessageCacheService->putPlayerInfo([
			'id' => $fakePlayerId,
			'fullName' => 'Cristian Ronaldo',
			'shortName' => 'c. Ronaldo'
		]);

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
					"person": "%s"
				 },
				"metadata": {
					"type": "player",
					"membership": [
						{
							"id": "0010",
							"teamId": "00020",
							"teamType": "club",
							"dateFrom": null,
							"dateTo": null,
							"onLoanFrom": null
						},
						{
							"id": "0020",
							"teamId": "00030",
							"teamType": "club",
							"dateFrom": "2021-02-10",
							"dateTo": "2022-02-10",
							"onLoanFrom": null
						},
						{
							"id": "0030",
							"teamId": "00040",
							"teamType": "club",
							"dateFrom": "2022-05-10",
							"dateTo": "2022-12-10",
							"onLoanFrom": "000010"
						},
						{
							"id": "0040",
							"teamId": "00050",
							"teamType": "national",
							"dateFrom": null,
							"dateTo": null,
							"onLoanFrom": null
						}
					]
				}
			}
		}',
			config('mediator-event.events.membership_was_updated'),
			Carbon::now()->format('c'),
			$fakePlayerId);

		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');

		/** Persist team items. */
		foreach ([00020, 00030, 00040, 00050] as $teamId) {
			$fakeTeamToModel = $this->createTeamModel()
				->setId($teamId);
			$this->teamRepository->persist($fakeTeamToModel);
		}

		/** Handle event. */
		app(MembershipWasUpdated::class)->handle($message);

		/**
		 * Read from DB
		 * @var Transfer $transfer
		 */
		$transfers = $this->transferRepository->findByPersonId($message->getBody()->getIdentifiers()['person']);
		$this->assertNotEmpty($transfers);
		$this->assertCount(3, $transfers);

		/** Consume question message for get player info from player_service. */
		$response = $this->brokerService->consumePureMessage([config('broker.queues.question')], 10);
		$this->assertEmpty($response);
	}

	public function testMembershipWasUpdatedHandleWithNullIdentifier()
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
					"person": ""
				 },
				"metadata": {
					"type": "player",
					"membership": [
						{
							"id": "0010",
							"teamId": "00020",
							"teamType": "club",
							"dateFrom": null,
							"dateTo": null,
							"onLoanFrom": null
						},
						{
							"id": "0020",
							"teamId": "00030",
							"teamType": "club",
							"dateFrom": "2021-02-10",
							"dateTo": "2022-02-10",
							"onLoanFrom": null
						},
						{
							"id": "0030",
							"teamId": "00040",
							"teamType": "club",
							"dateFrom": "2022-05-10",
							"dateTo": "2022-12-10",
							"onLoanFrom": "000010"
						},
						{
							"id": "0040",
							"teamId": "00050",
							"teamType": "national",
							"dateFrom": null,
							"dateTo": null,
							"onLoanFrom": null
						}
					]
				}
			}
		}',
			config('mediator-event.events.membership_was_updated'),
			Carbon::now()->format('c'));
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		/**
		 * Handle event.
		 */
		app(MembershipWasUpdated::class)->handle($message);
	}

	public function testMembershipWasUpdatedHandleWithNullMetaData()
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
					"person": "%s"
				 },
				"metadata": {
					"type": "",
					"membership": []
				}
			}
		}',
			config('mediator-event.events.membership_was_updated'),
			Carbon::now()->format('c'),
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		/** Handle event. */
		app(MembershipWasUpdated::class)->handle($message);
	}

	public function testMembershipWasUpdatedHandleWhenTeamItemsNotExist()
	{
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
					"person": "%s"
				 },
				"metadata": {
					"type": "player",
					"membership": [
						{
							"id": "0010",
							"teamId": "00020",
							"teamType": "club",
							"dateFrom": null,
							"dateTo": null,
							"onLoanFrom": null
						},
						{
							"id": "0020",
							"teamId": "00030",
							"teamType": "club",
							"dateFrom": "2021-02-10",
							"dateTo": "2022-02-10",
							"onLoanFrom": null
						},
						{
							"id": "0030",
							"teamId": "00040",
							"teamType": "club",
							"dateFrom": "2022-05-10",
							"dateTo": "2022-12-10",
							"onLoanFrom": "000010"
						},
						{
							"id": "0040",
							"teamId": "00050",
							"teamType": "national",
							"dateFrom": null,
							"dateTo": null,
							"onLoanFrom": null
						}
					]
				}
			}
		}',
			config('mediator-event.events.membership_was_updated'),
			Carbon::now()->format('c'),
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		/**
		 * Handle event.
		 */
		app(MembershipWasUpdated::class)->handle($message);

		/**
		 * Read from DB
		 * @var Transfer $transfer
		 */
		$transfers = $this->transferRepository->findByPersonId($message->getBody()->getIdentifiers()['person']);
		$this->assertNotEmpty($transfers);
		$this->assertCount(3, $transfers);
		foreach ($transfers as $transfer) {
			$this->assertNull($transfer->getTeamName());
			$this->assertNull($transfer->getOnLoanFromName());
		}

		/** Consume question message for get player info from player_service. */
		$response = $this->brokerService->consumePureMessage([config('broker.queues.question')], 10);
		$this->assertNotEmpty($response);
		$this->assertCount(3, $response);
	}

	public function testMembershipWasUpdatedHandleWithEmptyPlayerInfo()
	{
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
					"person": "%s"
				 },
				"metadata": {
					"type": "player",
					"membership": [
						{
							"id": "0010",
							"teamId": "00020",
							"teamType": "club",
							"dateFrom": null,
							"dateTo": null,
							"onLoanFrom": null
						},
						{
							"id": "0020",
							"teamId": "00030",
							"teamType": "club",
							"dateFrom": "2021-02-10",
							"dateTo": "2022-02-10",
							"onLoanFrom": null
						},
						{
							"id": "0030",
							"teamId": "00040",
							"teamType": "club",
							"dateFrom": "2022-05-10",
							"dateTo": "2022-12-10",
							"onLoanFrom": "000010"
						},
						{
							"id": "0040",
							"teamId": "00050",
							"teamType": "national",
							"dateFrom": null,
							"dateTo": null,
							"onLoanFrom": null
						}
					]
				}
			}
		}',
			config('mediator-event.events.membership_was_updated'),
			Carbon::now()->format('c'),
			$this->faker->uuid);

		/**
		 * @var Message $message
		 */

		$message = $this->serializer->deserialize($message, Message::class, 'json');

		/** Persist team items. */
		foreach ([00020, 00030, 00040, 00050] as $teamId) {
			$fakeTeamToModel = $this->createTeamModel()
				->setId($teamId);
			$this->teamRepository->persist($fakeTeamToModel);
		}

		/**
		 * Handle event.
		 */
		app(MembershipWasUpdated::class)->handle($message);

		/**
		 * Read from DB
		 * @var Transfer $transfer
		 */
		$transfers = $this->transferRepository->findByPersonId($message->getBody()->getIdentifiers()['person']);
		$this->assertNotEmpty($transfers);
		$this->assertCount(3, $transfers);

		/** Consume question message for get player info from player_service. */
		$response = $this->brokerService->consumePureMessage([config('broker.queues.question')], 10);
		$this->assertNotEmpty($response);

		$playerMessage = json_decode(json_encode($response[0]), true);
		$payload = json_decode($playerMessage, true);
		$this->assertEquals(config('broker.services.team_name'), $payload['headers']['source']);
		$this->assertEquals(config('broker.services.player_name'), $payload['headers']['destination']);
		$this->assertEquals(MembershipWasUpdatedProjectorListener::BROKER_EVENT_KEY, $payload['headers']['key']);
		$this->assertNotNull($payload['headers']['id']);
		$this->assertEquals(config('broker.services.player_name'), $payload['body']['entity']);
		$this->assertEquals($message->getBody()->getIdentifiers()['person'], $payload['body']['id']);

		/** Produce answer message from player service for update player info in transfer model. */
		$answerMessage = (new CommandQueryMessage())
			->setHeaders(
				(new Headers())
					->setEventId('1')
					->setKey(MembershipWasUpdatedProjectorListener::BROKER_EVENT_KEY)
					->setId($payload['headers']['id'])
					->setDestination(config('broker.services.team_name'))
					->setSource(config('broker.services.player_name'))
					->setDate(Carbon::now()->format('c'))
			)->setBody([]);
		/**
		 * Handle answer message from player service for update player info in transfer model.
		 */
		app(MembershipWasUpdatedUpdateInfo::class)->handle($answerMessage);
		/**
		 * Check player info is update in transfer model.
		 * @var Transfer $transfer
		 */
		$transfer = $this->transferRepository->findByPersonId($message->getBody()->getIdentifiers()['person']);
		$this->assertNotEmpty($transfer);
		$transfer = $transfer[0];
		$this->assertNull($transfer->getPersonName());
	}

	protected function tearDown(): void
	{
		$this->brokerMessageCacheService->flush();
		$this->teamRepository->drop();
		$this->transferRepository->drop();
	}
}