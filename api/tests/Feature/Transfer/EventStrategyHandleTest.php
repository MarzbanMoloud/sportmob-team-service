<?php


namespace Tests\Feature\Transfer;


use App\Exceptions\Projection\ProjectionException;
use App\Models\ReadModels\Transfer;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TransferRepository;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
use App\Services\Cache\TransferCacheService;
use App\Services\EventStrategy\MembershipWasUpdated;
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

	public function testMembershipWasUpdatedHandle()
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
							"teamId": "10020",
							"teamType": "club",
							"dateFrom": "2021-02-10",
							"dateTo": "2022-02-10",
							"onLoanFrom": null
						},
						{
							"id": "0020",
							"teamId": "10030",
							"teamType": "club",
							"dateFrom": "2021-02-10",
							"dateTo": "2022-02-10",
							"onLoanFrom": null
						},
						{
							"id": "0030",
							"teamId": "10040",
							"teamType": "club",
							"dateFrom": "2022-05-10",
							"dateTo": "2022-12-10",
							"onLoanFrom": "100010"
						},
						{
							"id": "0040",
							"teamId": "10050",
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
		foreach ([10020, 10030, 10040, 10050, 100010] as $teamId) {
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
		$this->assertCount(4, $transfers);
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
							"teamId": "10020",
							"teamType": "club",
							"dateFrom": "2021-02-10",
							"dateTo": "2022-02-10",
							"onLoanFrom": null
						},
						{
							"id": "0020",
							"teamId": "10030",
							"teamType": "club",
							"dateFrom": "2021-02-10",
							"dateTo": "2022-02-10",
							"onLoanFrom": null
						},
						{
							"id": "0030",
							"teamId": "10040",
							"teamType": "club",
							"dateFrom": "2022-05-10",
							"dateTo": "2022-12-10",
							"onLoanFrom": "100010"
						},
						{
							"id": "0040",
							"teamId": "10050",
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
		$this->assertCount(4, $transfers);
		foreach ($transfers as $transfer) {
			$this->assertNull($transfer->getToTeamName());
			$this->assertNull($transfer->getFromTeamName());
		}
	}

	protected function tearDown(): void
	{
		$this->brokerMessageCacheService->flush();
		$this->teamRepository->drop();
		$this->transferRepository->drop();
	}
}