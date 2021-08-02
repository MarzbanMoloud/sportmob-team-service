<?php


namespace Tests\Feature\Transfer;


use App\Exceptions\Projection\ProjectionException;
use App\Models\ReadModels\Transfer;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TransferRepository;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
use App\Services\Cache\Interfaces\TransferCacheServiceInterface;
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
		$this->transferCacheService->putTransfersByTeam(10237, '2021-2022', ['test']);

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
							"id":"10347",
							"teamId":"10237",
							"teamName":"Malmoe FF",
							"teamType":"club",
							"dateFrom":"1999-01-01",
							"dateTo":"2001-01-01",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"10348",
							"teamId":"8593",
							"teamName":"Ajax",
							"teamType":"club",
							"dateFrom":"2001-01-01",
							"dateTo":"2004-01-01",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"10346",
							"teamId":"9885",
							"teamName":"Juventus",
							"teamType":"club",
							"dateFrom":"2004-09-12",
							"dateTo":"2006-08-08",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"10349",
							"teamId":"8636",
							"teamName":"Inter",
							"teamType":"club",
							"dateFrom":"2006-08-10",
							"dateTo":"2009-07-27",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"550828",
							"teamId":"8634",
							"teamName":"Barcelona",
							"teamType":"club",
							"dateFrom":"2009-07-27",
							"dateTo":"2011-06-30",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"628580",
							"teamId":"8564",
							"teamName":"AC Milan",
							"teamType":"club",
							"dateFrom":"2010-08-28",
							"dateTo":"2011-06-30",
							"active":"no",
							"onLoanFrom":"8634",
							"onLoanTo":null
						},
						{
							"id":"817346",
							"teamId":"8564",
							"teamName":"AC Milan",
							"teamType":"club",
							"dateFrom":"2011-07-01",
							"dateTo":"2012-07-17",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"1003995",
							"teamId":"9847",
							"teamName":"Paris Saint-Germain",
							"teamType":"club",
							"dateFrom":"2012-07-17",
							"dateTo":"2016-06-30",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"2137930",
							"teamId":"10260",
							"teamName":"Manchester United",
							"teamType":"club",
							"dateFrom":"2016-07-01",
							"dateTo":"2017-07-01",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"2513463",
							"teamId":"10260",
							"teamName":"Manchester United",
							"teamType":"club",
							"dateFrom":"2017-08-24",
							"dateTo":"2018-03-22",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"2706023",
							"teamId":"6637",
							"teamName":"LA Galaxy",
							"teamType":"club",
							"dateFrom":"2018-03-23",
							"dateTo":"2019-10-25",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"3241141",
							"teamId":"8564",
							"teamName":"AC Milan",
							"teamType":"club",
							"dateFrom":"2019-12-27",
							"dateTo":null,
							"active":"yes",
							"onLoanFrom":null,
							"onLoanTo":null
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
		foreach ([10237, 8593, 9885, 8636, 8634, 8564, 9847, 10260, 6637] as $teamId) {
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
		$this->assertCount(13, $transfers);
		foreach ($transfers as $transfer) {
			$this->assertInstanceOf(Transfer::class, $transfer);
		}
		/**
		 * Read from Cache (Per person)
		 * @var Transfer $transfer
		 */
		$transfers = $this->transferCacheService->get(sprintf(TransferCacheServiceInterface::TRANSFER_BY_PERSON_KEY, $message->getBody()->getIdentifiers()['person']));
		$this->assertNotEmpty($transfers);
		$this->assertCount(13, $transfers);
		foreach ($transfers as $transfer) {
			$this->assertInstanceOf(Transfer::class, $transfer);
		}
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
							"id":"10347",
							"teamId":"10237",
							"teamName":"Malmoe FF",
							"teamType":"club",
							"dateFrom":"1999-01-01",
							"dateTo":"2001-01-01",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"10348",
							"teamId":"8593",
							"teamName":"Ajax",
							"teamType":"club",
							"dateFrom":"2001-01-01",
							"dateTo":"2004-01-01",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"10346",
							"teamId":"9885",
							"teamName":"Juventus",
							"teamType":"club",
							"dateFrom":"2004-09-12",
							"dateTo":"2006-08-08",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"10349",
							"teamId":"8636",
							"teamName":"Inter",
							"teamType":"club",
							"dateFrom":"2006-08-10",
							"dateTo":"2009-07-27",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"550828",
							"teamId":"8634",
							"teamName":"Barcelona",
							"teamType":"club",
							"dateFrom":"2009-07-27",
							"dateTo":"2011-06-30",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"628580",
							"teamId":"8564",
							"teamName":"AC Milan",
							"teamType":"club",
							"dateFrom":"2010-08-28",
							"dateTo":"2011-06-30",
							"active":"no",
							"onLoanFrom":"8634",
							"onLoanTo":null
						},
						{
							"id":"817346",
							"teamId":"8564",
							"teamName":"AC Milan",
							"teamType":"club",
							"dateFrom":"2011-07-01",
							"dateTo":"2012-07-17",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"1003995",
							"teamId":"9847",
							"teamName":"Paris Saint-Germain",
							"teamType":"club",
							"dateFrom":"2012-07-17",
							"dateTo":"2016-06-30",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"2137930",
							"teamId":"10260",
							"teamName":"Manchester United",
							"teamType":"club",
							"dateFrom":"2016-07-01",
							"dateTo":"2017-07-01",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"2513463",
							"teamId":"10260",
							"teamName":"Manchester United",
							"teamType":"club",
							"dateFrom":"2017-08-24",
							"dateTo":"2018-03-22",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"2706023",
							"teamId":"6637",
							"teamName":"LA Galaxy",
							"teamType":"club",
							"dateFrom":"2018-03-23",
							"dateTo":"2019-10-25",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"3241141",
							"teamId":"8564",
							"teamName":"AC Milan",
							"teamType":"club",
							"dateFrom":"2019-12-27",
							"dateTo":null,
							"active":"yes",
							"onLoanFrom":null,
							"onLoanTo":null
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

		/** Handle event. */
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
					"type": "player",
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
							"id":"10347",
							"teamId":"10237",
							"teamName":"Malmoe FF",
							"teamType":"club",
							"dateFrom":"1999-01-01",
							"dateTo":"2001-01-01",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"10348",
							"teamId":"8593",
							"teamName":"Ajax",
							"teamType":"club",
							"dateFrom":"2001-01-01",
							"dateTo":"2004-01-01",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"10346",
							"teamId":"9885",
							"teamName":"Juventus",
							"teamType":"club",
							"dateFrom":"2004-09-12",
							"dateTo":"2006-08-08",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"10349",
							"teamId":"8636",
							"teamName":"Inter",
							"teamType":"club",
							"dateFrom":"2006-08-10",
							"dateTo":"2009-07-27",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"550828",
							"teamId":"8634",
							"teamName":"Barcelona",
							"teamType":"club",
							"dateFrom":"2009-07-27",
							"dateTo":"2011-06-30",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"628580",
							"teamId":"8564",
							"teamName":"AC Milan",
							"teamType":"club",
							"dateFrom":"2010-08-28",
							"dateTo":"2011-06-30",
							"active":"no",
							"onLoanFrom":"8634",
							"onLoanTo":null
						},
						{
							"id":"817346",
							"teamId":"8564",
							"teamName":"AC Milan",
							"teamType":"club",
							"dateFrom":"2011-07-01",
							"dateTo":"2012-07-17",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"1003995",
							"teamId":"9847",
							"teamName":"Paris Saint-Germain",
							"teamType":"club",
							"dateFrom":"2012-07-17",
							"dateTo":"2016-06-30",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"2137930",
							"teamId":"10260",
							"teamName":"Manchester United",
							"teamType":"club",
							"dateFrom":"2016-07-01",
							"dateTo":"2017-07-01",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"2513463",
							"teamId":"10260",
							"teamName":"Manchester United",
							"teamType":"club",
							"dateFrom":"2017-08-24",
							"dateTo":"2018-03-22",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"2706023",
							"teamId":"6637",
							"teamName":"LA Galaxy",
							"teamType":"club",
							"dateFrom":"2018-03-23",
							"dateTo":"2019-10-25",
							"active":"no",
							"onLoanFrom":null,
							"onLoanTo":null
						},
						{
							"id":"3241141",
							"teamId":"8564",
							"teamName":"AC Milan",
							"teamType":"club",
							"dateFrom":"2019-12-27",
							"dateTo":null,
							"active":"yes",
							"onLoanFrom":null,
							"onLoanTo":null
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

		/** Handle event. */
		app(MembershipWasUpdated::class)->handle($message);

		/**
		 * Read from DB
		 * @var Transfer $transfer
		 */
		$transfers = $this->transferRepository->findByPersonId($message->getBody()->getIdentifiers()['person']);
		$this->assertNotEmpty($transfers);
		$this->assertCount(13, $transfers);
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