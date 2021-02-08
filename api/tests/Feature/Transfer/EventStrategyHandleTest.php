<?php


namespace Tests\Feature\Transfer;


use App\Exceptions\Projection\ProjectionException;
use App\Listeners\Projection\PlayerWasTransferredProjectorListener;
use App\Models\ReadModels\Transfer;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TransferRepository;
use App\Services\BrokerCommandStrategy\PlayerWasTransferredUpdateInfo;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
use App\Services\Cache\TransferCacheService;
use App\Services\EventStrategy\PlayerWasTransferred;
use App\ValueObjects\Broker\CommandQuery\Headers;
use App\ValueObjects\Broker\CommandQuery\Message as CommandQueryMessage;
use App\ValueObjects\Broker\Mediator\Message;
use Carbon\Carbon;
use DateTimeInterface;
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

	public function testTeamWasCreatedHandleWhenActiveIsTrue()
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
					"player": "%s",
					"from": "%s",
					"to": "%s"
				 },
				"metadata": {
					"startDate": "2020-02-10",
					"endDate": "2021-02-11",
					"active": true,
					"type": "transferred"
				}
			}
		}',
		config('mediator-event.events.player_was_transferred'),
		Carbon::now()->toDateTimeString(),
		$this->faker->uuid,
		$this->faker->uuid,
		$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		/**
		 * Persist team items for 'to' and 'from' fields.
		 */
		$fakeTeamToModel = $this->createTeamModel()->setId($message->getBody()->getIdentifiers()['to']);
		$this->teamRepository->persist($fakeTeamToModel);
		$fakeTeamFromModel = $this->createTeamModel()->setId($message->getBody()->getIdentifiers()['from']);
		$this->teamRepository->persist($fakeTeamFromModel);
		/**
		 * Handle event.
		 */
		app(PlayerWasTransferred::class)->handle($message->getBody());
		/**
		 * Read from DB
		 * @var Transfer $transfer
		 */
		$transfer = $this->transferRepository->findByPlayerId($message->getBody()->getIdentifiers()['player']);
		$this->assertNotEmpty($transfer);
		$transfer = $transfer[0];
		$this->assertEquals($message->getBody()->getIdentifiers()['player'], $transfer->getPlayerId());
		$this->assertEquals($message->getBody()->getIdentifiers()['from'], $transfer->getFromTeamId());
		$this->assertEquals($message->getBody()->getIdentifiers()['to'], $transfer->getToTeamId());
		$this->assertEquals($fakeTeamToModel->getName()->getOriginal(), $transfer->getToTeamName());
		$this->assertEquals($fakeTeamFromModel->getName()->getOriginal(), $transfer->getFromTeamName());
		$this->assertNull($transfer->getPlayerName());
		$this->assertNull($transfer->getPlayerPosition());
		$this->assertNull($transfer->getMarketValue());
		$this->assertNotNull($transfer->getStartDate());
		$this->assertNotNull($transfer->getEndDate());
		$this->assertNull($transfer->getAnnouncedDate());
		$this->assertNull($transfer->getContractDate());
		$this->assertEquals($message->getBody()->getMetadata()['type'], $transfer->getType());
		$this->assertTrue($transfer->isActive());
		$this->assertEquals(0, $transfer->getLike());
		$this->assertEquals(0, $transfer->getDislike());
		$this->assertNotNull($transfer->getSeason());
		/**
		 * Consume question message for get player info from player_service.
		 */
		$response = $this->brokerService->consumePureMessage([config('broker.queues.question')], 10);
		$this->assertNotEmpty($response);
		$playerMessage = json_decode(json_encode($response[0]), true);
		$payload = json_decode($playerMessage, true);
		$this->assertEquals(config('broker.services.team_name'), $payload['headers']['source']);
		$this->assertEquals(config('broker.services.player_name'), $payload['headers']['destination']);
		$this->assertEquals(PlayerWasTransferredProjectorListener::BROKER_EVENT_KEY, $payload['headers']['key']);
		$this->assertEquals(
			sprintf('%s#%s', $message->getBody()->getIdentifiers()['player'], $transfer->getStartDate()->format(DateTimeInterface::ATOM))
			, $payload['headers']['id']);
		$this->assertEquals(config('broker.services.player_name'), $payload['body']['entity']);
		$this->assertEquals($message->getBody()->getIdentifiers()['player'], $payload['body']['id']);
		/**
		 * Produce answer message from player service for update player info in transfer model.
		 */
		$answerMessage = (new CommandQueryMessage())
			->setHeaders(
				(new Headers())
					->setKey(PlayerWasTransferredProjectorListener::BROKER_EVENT_KEY)
					->setId($payload['headers']['id'])
					->setDestination(config('broker.services.team_name'))
					->setSource(config('broker.services.player_name'))
					->setDate(Carbon::now()->toDateTimeString())
			)->setBody([
				'entity' => config('broker.services.player_name'),
				'id' => $message->getBody()->getIdentifiers()['player'],
				'fullName' => $this->faker->name,
				'shortName' => $this->faker->name,
				'position' => 'defender'
			]);
		/**
		 * Handle answer message from player service for update player info in transfer model.
		 */
		app(PlayerWasTransferredUpdateInfo::class)->handle($answerMessage);
		/**
		 * Check player info is update in transfer model.
		 * @var Transfer $transfer
		 */
		$transfer = $this->transferRepository->findByPlayerId($message->getBody()->getIdentifiers()['player']);
		$this->assertNotEmpty($transfer);
		$transfer = $transfer[0];
		$this->assertEquals($answerMessage->getBody()['fullName'], $transfer->getPlayerName());
		$this->assertEquals($answerMessage->getBody()['position'], $transfer->getPlayerPosition());
		/**
		 * Check broker message cache for player info.
		 */
		$brokerMessageCache = $this->brokerMessageCacheService->getPlayerInfo($message->getBody()->getIdentifiers()['player']);
		$this->assertEquals($answerMessage->getBody()['fullName'], $brokerMessageCache['fullName']);
		$this->assertEquals($answerMessage->getBody()['shortName'], $brokerMessageCache['shortName']);
		$this->assertEquals($answerMessage->getBody()['position'], $brokerMessageCache['position']);
		/**
		 * Consume Notification message.
		 */
		$response = $this->brokerService->consumePureMessage([config('broker.queues.notification')], 10);
		$NotificationMessage = json_decode(json_encode($response[0]), true);
		$payload = json_decode($NotificationMessage, true);
		$this->assertNotEmpty($payload);
		$this->assertCount(2, $payload['body']);
		$this->assertCount(3, $payload['body']['id']);
		$this->assertNotNull($payload['body']['id']['player']);
		$this->assertNotNull($payload['body']['id']['to']);
		$this->assertNotNull($payload['body']['id']['from']);
		$this->assertCount(7, $payload['body']['metadata']);
		$this->assertNotNull($payload['body']['metadata']['playerPosition']);
		$this->assertNotNull($payload['body']['metadata']['playerName']);
		$this->assertNotNull($payload['body']['metadata']['fromTeamName']);
		$this->assertNotNull($payload['body']['metadata']['toTeamName']);
		$this->assertNotNull($payload['body']['metadata']['startDate']);
		$this->assertNotNull($payload['body']['metadata']['endDate']);
		$this->assertNotNull($payload['body']['metadata']['season']);
		$this->assertCount(2, $payload['headers']);
		$this->assertNotNull($payload['headers']['event']);
		$this->assertNotNull($payload['headers']['date']);
		/**
		 * Read from Cache
		 */
		$this->transferRepository->drop();
		$playerTransfer = app('cache')->get($this->transferCacheService->getTransferByPlayerKey($message->getBody()->getIdentifiers()['player']));
		$this->assertInstanceOf(Transfer::class, $playerTransfer[0]);
		$teamTransfer = app('cache')->get(
			$this->transferCacheService->getTransferByTeamKey($message->getBody()->getIdentifiers()['to'], '2019-2020'),
		);
		$this->assertInstanceOf(Transfer::class, $teamTransfer[0]);
	}

	public function testTeamWasCreatedHandleWhenPlayerCacheExist()
	{
		$fakePlayerId = $this->faker->uuid;
		$this->brokerMessageCacheService->putPlayerInfo([
			'id' => $fakePlayerId,
			'fullName' => 'Cristian Ronaldo',
			'shortName' => 'c. Ronaldo',
			'position' => 'defender'
		]);
		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s"
            },
			"body":{
				"identifiers": {
					"player": "%s",
					"from": "%s",
					"to": "%s"
				 },
				"metadata": {
					"startDate": "2020-02-10",
					"endDate": "2021-02-11",
					"active": true,
					"type": "transferred"
				}
			}
		}',
			config('mediator-event.events.player_was_transferred'),
			Carbon::now()->toDateTimeString(),
			$fakePlayerId,
			$this->faker->uuid,
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		/**
		 * Persist team items for 'to' and 'from' fields.
		 */
		$fakeTeamToModel = $this->createTeamModel()->setId($message->getBody()->getIdentifiers()['to']);
		$this->teamRepository->persist($fakeTeamToModel);
		$fakeTeamFromModel = $this->createTeamModel()->setId($message->getBody()->getIdentifiers()['from']);
		$this->teamRepository->persist($fakeTeamFromModel);
		/**
		 * Handle event.
		 */
		app(PlayerWasTransferred::class)->handle($message->getBody());
		/**
		 * Read from DB
		 * @var Transfer $transfer
		 */
		$transfer = $this->transferRepository->findByPlayerId($message->getBody()->getIdentifiers()['player']);
		$this->assertNotEmpty($transfer);
		$transfer = $transfer[0];
		$this->assertEquals($message->getBody()->getIdentifiers()['player'], $transfer->getPlayerId());
		$this->assertEquals($message->getBody()->getIdentifiers()['from'], $transfer->getFromTeamId());
		$this->assertEquals($message->getBody()->getIdentifiers()['to'], $transfer->getToTeamId());
		$this->assertEquals($fakeTeamToModel->getName()->getOriginal(), $transfer->getToTeamName());
		$this->assertEquals($fakeTeamFromModel->getName()->getOriginal(), $transfer->getFromTeamName());
		$this->assertEquals('Cristian Ronaldo', $transfer->getPlayerName());
		$this->assertEquals('defender', $transfer->getPlayerPosition());
		$this->assertNull($transfer->getMarketValue());
		$this->assertNotNull($transfer->getStartDate());
		$this->assertNotNull($transfer->getEndDate());
		$this->assertNull($transfer->getAnnouncedDate());
		$this->assertNull($transfer->getContractDate());
		$this->assertEquals($message->getBody()->getMetadata()['type'], $transfer->getType());
		$this->assertTrue($transfer->isActive());
		$this->assertEquals(0, $transfer->getLike());
		$this->assertEquals(0, $transfer->getDislike());
		$this->assertNotNull($transfer->getSeason());
		/**
		 * Consume question message for get player info from player_service.
		 */
		$response = $this->brokerService->consumePureMessage([config('broker.queues.question')], 10);
		$this->assertEmpty($response);
		/**
		 * Consume Notification message.
		 */
		$response = $this->brokerService->consumePureMessage([config('broker.queues.notification')], 10);
		$NotificationMessage = json_decode(json_encode($response[0]), true);
		$payload = json_decode($NotificationMessage, true);
		$this->assertNotEmpty($payload);
		$this->assertCount(2, $payload['body']);
		$this->assertCount(3, $payload['body']['id']);
		$this->assertNotNull($payload['body']['id']['player']);
		$this->assertNotNull($payload['body']['id']['to']);
		$this->assertNotNull($payload['body']['id']['from']);
		$this->assertCount(7, $payload['body']['metadata']);
		$this->assertNotNull($payload['body']['metadata']['playerPosition']);
		$this->assertNotNull($payload['body']['metadata']['playerName']);
		$this->assertNotNull($payload['body']['metadata']['fromTeamName']);
		$this->assertNotNull($payload['body']['metadata']['toTeamName']);
		$this->assertNotNull($payload['body']['metadata']['startDate']);
		$this->assertNotNull($payload['body']['metadata']['endDate']);
		$this->assertNotNull($payload['body']['metadata']['season']);
		$this->assertCount(2, $payload['headers']);
		$this->assertNotNull($payload['headers']['event']);
		$this->assertNotNull($payload['headers']['date']);
	}

	public function testTeamWasCreatedHandleWhenActiveIsFalse()
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
					"player": "%s",
					"from": "%s",
					"to": "%s"
				 },
				"metadata": {
					"startDate": "2020-02-10",
					"endDate": "2021-02-11",
					"active": false,
					"type": "transferred"
				}
			}
		}',
			config('mediator-event.events.player_was_transferred'),
			Carbon::now()->toDateTimeString(),
			$this->faker->uuid,
			$this->faker->uuid,
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		/**
		 * Persist team items for 'to' and 'from' fields.
		 */
		$fakeTeamToModel = $this->createTeamModel()->setId($message->getBody()->getIdentifiers()['to']);
		$this->teamRepository->persist($fakeTeamToModel);
		$fakeTeamFromModel = $this->createTeamModel()->setId($message->getBody()->getIdentifiers()['from']);
		$this->teamRepository->persist($fakeTeamFromModel);
		/**
		 * Handle event.
		 */
		app(PlayerWasTransferred::class)->handle($message->getBody());
		/**
		 * Read from DB
		 * @var Transfer $transfer
		 */
		$transfer = $this->transferRepository->findByPlayerId($message->getBody()->getIdentifiers()['player']);
		$this->assertNotEmpty($transfer);
		$transfer = $transfer[0];
		$this->assertEquals($message->getBody()->getIdentifiers()['player'], $transfer->getPlayerId());
		$this->assertEquals($message->getBody()->getIdentifiers()['from'], $transfer->getFromTeamId());
		$this->assertEquals($message->getBody()->getIdentifiers()['to'], $transfer->getToTeamId());
		$this->assertEquals($fakeTeamToModel->getName()->getOriginal(), $transfer->getToTeamName());
		$this->assertEquals($fakeTeamFromModel->getName()->getOriginal(), $transfer->getFromTeamName());
		$this->assertNull($transfer->getPlayerName());
		$this->assertNull($transfer->getPlayerPosition());
		$this->assertNull($transfer->getMarketValue());
		$this->assertNotNull($transfer->getStartDate());
		$this->assertNotNull($transfer->getEndDate());
		$this->assertNull($transfer->getAnnouncedDate());
		$this->assertNull($transfer->getContractDate());
		$this->assertEquals($message->getBody()->getMetadata()['type'], $transfer->getType());
		$this->assertFalse($transfer->isActive());
		$this->assertEquals(0, $transfer->getLike());
		$this->assertEquals(0, $transfer->getDislike());
		$this->assertNotNull($transfer->getSeason());
		/**
		 * Consume question message for get player info from player_service.
		 */
		$response = $this->brokerService->consumePureMessage([config('broker.queues.question')], 10);
		$this->assertNotEmpty($response);
		$playerMessage = json_decode(json_encode($response[0]), true);
		$payload = json_decode($playerMessage, true);
		$this->assertEquals(config('broker.services.team_name'), $payload['headers']['source']);
		$this->assertEquals(config('broker.services.player_name'), $payload['headers']['destination']);
		$this->assertEquals(PlayerWasTransferredProjectorListener::BROKER_EVENT_KEY, $payload['headers']['key']);
		$this->assertEquals(
			sprintf('%s#%s', $message->getBody()->getIdentifiers()['player'], $transfer->getStartDate()->format(DateTimeInterface::ATOM))
			, $payload['headers']['id']);
		$this->assertEquals(config('broker.services.player_name'), $payload['body']['entity']);
		$this->assertEquals($message->getBody()->getIdentifiers()['player'], $payload['body']['id']);
		/**
		 * Produce answer message from player service for update player info in transfer model.
		 */
		$answerMessage = (new CommandQueryMessage())
			->setHeaders(
				(new Headers())
					->setKey(PlayerWasTransferredProjectorListener::BROKER_EVENT_KEY)
					->setId($payload['headers']['id'])
					->setDestination(config('broker.services.team_name'))
					->setSource(config('broker.services.player_name'))
					->setDate(Carbon::now()->toDateTimeString())
			)->setBody([
				'entity' => config('broker.services.player_name'),
				'id' => $message->getBody()->getIdentifiers()['player'],
				'fullName' => $this->faker->name,
				'shortName' => $this->faker->name,
				'position' => 'defender'
			]);
		/**
		 * Handle answer message from player service for update player info in transfer model.
		 */
		app(PlayerWasTransferredUpdateInfo::class)->handle($answerMessage);
		/**
		 * Check player info is update in transfer model.
		 * @var Transfer $transfer
		 */
		$transfer = $this->transferRepository->findByPlayerId($message->getBody()->getIdentifiers()['player']);
		$this->assertNotEmpty($transfer);
		$transfer = $transfer[0];
		$this->assertEquals($answerMessage->getBody()['fullName'], $transfer->getPlayerName());
		$this->assertEquals($answerMessage->getBody()['position'], $transfer->getPlayerPosition());
		/**
		 * Check broker message cache for player info.
		 */
		$brokerMessageCache = $this->brokerMessageCacheService->getPlayerInfo($message->getBody()->getIdentifiers()['player']);
		$this->assertEquals($answerMessage->getBody()['fullName'], $brokerMessageCache['fullName']);
		$this->assertEquals($answerMessage->getBody()['shortName'], $brokerMessageCache['shortName']);
		$this->assertEquals($answerMessage->getBody()['position'], $brokerMessageCache['position']);
	}

	public function testTeamWasCreatedHandleWithActiveTransfer()
	{
		$fakePlayerID = $this->faker->uuid;
		$fakeTransferModel = $this->createTransferModel()
			->setPlayerId($fakePlayerID)
			->setActive(true);
		$fakeTransferModel->prePersist();
		$this->transferRepository->persist($fakeTransferModel);
		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s"
            },
			"body":{
				"identifiers": {
					"player": "%s",
					"from": "%s",
					"to": "%s"
				 },
				"metadata": {
					"startDate": "2020-02-10",
					"endDate": "2021-02-11",
					"active": true,
					"type": "transferred"
				}
			}
		}',
			config('mediator-event.events.player_was_transferred'),
			Carbon::now()->toDateTimeString(),
			$fakePlayerID,
			$this->faker->uuid,
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		/**
		 * Persist team items for 'to' and 'from' fields.
		 */
		$fakeTeamToModel = $this->createTeamModel()->setId($message->getBody()->getIdentifiers()['to']);
		$this->teamRepository->persist($fakeTeamToModel);
		$fakeTeamFromModel = $this->createTeamModel()->setId($message->getBody()->getIdentifiers()['from']);
		$this->teamRepository->persist($fakeTeamFromModel);
		/**
		 * Handle event.
		 */
		app(PlayerWasTransferred::class)->handle($message->getBody());
		/**
		 * Read from DB
		 * @var Transfer $transfer
		 */
		$transfers = $this->transferRepository->findByPlayerId($message->getBody()->getIdentifiers()['player']);
		$this->assertNotEmpty($transfers);
		foreach ($transfers as $transfer) {
			if ($transfer->isActive()) {
				$this->assertEquals($message->getBody()->getIdentifiers()['player'], $transfer->getPlayerId());
				$this->assertEquals($message->getBody()->getIdentifiers()['from'], $transfer->getFromTeamId());
				$this->assertEquals($message->getBody()->getIdentifiers()['to'], $transfer->getToTeamId());
				$this->assertEquals($fakeTeamToModel->getName()->getOriginal(), $transfer->getToTeamName());
				$this->assertEquals($fakeTeamFromModel->getName()->getOriginal(), $transfer->getFromTeamName());
				$this->assertNull($transfer->getPlayerName());
				$this->assertNull($transfer->getPlayerPosition());
				$this->assertNull($transfer->getMarketValue());
				$this->assertNotNull($transfer->getStartDate());
				$this->assertNotNull($transfer->getEndDate());
				$this->assertNull($transfer->getAnnouncedDate());
				$this->assertNull($transfer->getContractDate());
				$this->assertEquals($message->getBody()->getMetadata()['type'], $transfer->getType());
				$this->assertTrue($transfer->isActive());
				$this->assertEquals(0, $transfer->getLike());
				$this->assertEquals(0, $transfer->getDislike());
				$this->assertNotNull($transfer->getSeason());
			} else {
				$this->assertEquals($fakePlayerID, $transfer->getPlayerId());
			}
		}

		/**
		 * Consume question message for get player info from player_service.
		 */
		$response = $this->brokerService->consumePureMessage([config('broker.queues.question')], 10);
		$this->assertNotEmpty($response);
		$playerMessage = json_decode(json_encode($response[0]), true);
		$payload = json_decode($playerMessage, true);
		$this->assertEquals(config('broker.services.team_name'), $payload['headers']['source']);
		$this->assertEquals(config('broker.services.player_name'), $payload['headers']['destination']);
		$this->assertEquals(PlayerWasTransferredProjectorListener::BROKER_EVENT_KEY, $payload['headers']['key']);
		$this->assertEquals(
			sprintf('%s#%s', $message->getBody()->getIdentifiers()['player'], $transfer->getStartDate()->format(DateTimeInterface::ATOM))
			, $payload['headers']['id']);
		$this->assertEquals(config('broker.services.player_name'), $payload['body']['entity']);
		$this->assertEquals($message->getBody()->getIdentifiers()['player'], $payload['body']['id']);
		/**
		 * Produce answer message from player service for update player info in transfer model.
		 */
		$answerMessage = (new CommandQueryMessage())
			->setHeaders(
				(new Headers())
					->setKey(PlayerWasTransferredProjectorListener::BROKER_EVENT_KEY)
					->setId($payload['headers']['id'])
					->setDestination(config('broker.services.team_name'))
					->setSource(config('broker.services.player_name'))
					->setDate(Carbon::now()->toDateTimeString())
			)->setBody([
				'entity' => config('broker.services.player_name'),
				'id' => $message->getBody()->getIdentifiers()['player'],
				'fullName' => $this->faker->name,
				'shortName' => $this->faker->name,
				'position' => 'defender'
			]);
		/**
		 * Handle answer message from player service for update player info in transfer model.
		 */
		app(PlayerWasTransferredUpdateInfo::class)->handle($answerMessage);
		/**
		 * Check player info is update in transfer model.
		 * @var Transfer $transfer
		 */
		[$playerId, $startDate] = explode('#', $payload['headers']['id']);
		$transfer = $this->transferRepository->find(['playerId' => $playerId, 'startDate' => $startDate]);
		$this->assertNotEmpty($transfer);
		$this->assertEquals($answerMessage->getBody()['fullName'], $transfer->getPlayerName());
		$this->assertEquals($answerMessage->getBody()['position'], $transfer->getPlayerPosition());
		/**
		 * Check broker message cache for player info.
		 */
		$brokerMessageCache = $this->brokerMessageCacheService->getPlayerInfo($message->getBody()->getIdentifiers()['player']);
		$this->assertEquals($answerMessage->getBody()['fullName'], $brokerMessageCache['fullName']);
		$this->assertEquals($answerMessage->getBody()['shortName'], $brokerMessageCache['shortName']);
		$this->assertEquals($answerMessage->getBody()['position'], $brokerMessageCache['position']);
		/**
		 * Consume Notification message.
		 */
		$response = $this->brokerService->consumePureMessage([config('broker.queues.notification')], 10);
		$NotificationMessage = json_decode(json_encode($response[0]), true);
		$payload = json_decode($NotificationMessage, true);
		$this->assertNotEmpty($payload);
		$this->assertCount(2, $payload['body']);
		$this->assertCount(3, $payload['body']['id']);
		$this->assertNotNull($payload['body']['id']['player']);
		$this->assertNotNull($payload['body']['id']['to']);
		$this->assertNotNull($payload['body']['id']['from']);
		$this->assertCount(7, $payload['body']['metadata']);
		$this->assertNotNull($payload['body']['metadata']['playerPosition']);
		$this->assertNotNull($payload['body']['metadata']['playerName']);
		$this->assertNotNull($payload['body']['metadata']['fromTeamName']);
		$this->assertNotNull($payload['body']['metadata']['toTeamName']);
		$this->assertNotNull($payload['body']['metadata']['startDate']);
		$this->assertNotNull($payload['body']['metadata']['endDate']);
		$this->assertNotNull($payload['body']['metadata']['season']);
		$this->assertCount(2, $payload['headers']);
		$this->assertNotNull($payload['headers']['event']);
		$this->assertNotNull($payload['headers']['date']);
	}

	public function testTeamWasCreatedHandleWithNullIdentifier()
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
					"player": "",
					"from": "",
					"to": ""
				 },
				"metadata": {}
			}
		}',
			config('mediator-event.events.player_was_transferred'),
			Carbon::now()->toDateTimeString());
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		/**
		 * Handle event.
		 */
		app(PlayerWasTransferred::class)->handle($message->getBody());
	}

	public function testTeamWasCreatedHandleWithNullMetaData()
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
					"player": "%s",
					"from": "%s",
					"to": "%s"
				 },
				"metadata": {
					"startDate": "",
					"endDate": "",
					"active": "",
					"type": ""
				}
			}
		}',
			config('mediator-event.events.player_was_transferred'),
			Carbon::now()->toDateTimeString(),
			$this->faker->uuid,
			$this->faker->uuid,
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		/**
		 * Handle event.
		 */
		app(PlayerWasTransferred::class)->handle($message->getBody());
	}

	public function testTeamWasCreatedHandleWhenTeamItemsNotExist()
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
					"player": "%s",
					"from": "%s",
					"to": "%s"
				 },
				"metadata": {
					"startDate": "2020-02-10",
					"endDate": "2021-02-11",
					"active": true,
					"type": "transferred"
				}
			}
		}',
			config('mediator-event.events.player_was_transferred'),
			Carbon::now()->toDateTimeString(),
			$this->faker->uuid,
			$this->faker->uuid,
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		/**
		 * Handle event.
		 */
		app(PlayerWasTransferred::class)->handle($message->getBody());
	}

	public function testTeamWasCreatedHandleWithEmptyPlayerInfo()
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
					"player": "%s",
					"from": "%s",
					"to": "%s"
				 },
				"metadata": {
					"startDate": "2020-02-10",
					"endDate": "2021-02-11",
					"active": true,
					"type": "transferred"
				}
			}
		}',
			config('mediator-event.events.player_was_transferred'),
			Carbon::now()->toDateTimeString(),
			$this->faker->uuid,
			$this->faker->uuid,
			$this->faker->uuid);
		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');
		/**
		 * Persist team items for 'to' and 'from' fields.
		 */
		$fakeTeamToModel = $this->createTeamModel()->setId($message->getBody()->getIdentifiers()['to']);
		$this->teamRepository->persist($fakeTeamToModel);
		$fakeTeamFromModel = $this->createTeamModel()->setId($message->getBody()->getIdentifiers()['from']);
		$this->teamRepository->persist($fakeTeamFromModel);
		/**
		 * Handle event.
		 */
		app(PlayerWasTransferred::class)->handle($message->getBody());
		/**
		 * Read from DB
		 * @var Transfer $transfer
		 */
		$transfer = $this->transferRepository->findByPlayerId($message->getBody()->getIdentifiers()['player']);
		$this->assertNotEmpty($transfer);
		$transfer = $transfer[0];
		$this->assertEquals($message->getBody()->getIdentifiers()['player'], $transfer->getPlayerId());
		$this->assertEquals($message->getBody()->getIdentifiers()['from'], $transfer->getFromTeamId());
		$this->assertEquals($message->getBody()->getIdentifiers()['to'], $transfer->getToTeamId());
		$this->assertEquals($fakeTeamToModel->getName()->getOriginal(), $transfer->getToTeamName());
		$this->assertEquals($fakeTeamFromModel->getName()->getOriginal(), $transfer->getFromTeamName());
		$this->assertNull($transfer->getPlayerName());
		$this->assertNull($transfer->getPlayerPosition());
		$this->assertNull($transfer->getMarketValue());
		$this->assertNotNull($transfer->getStartDate());
		$this->assertNotNull($transfer->getEndDate());
		$this->assertNull($transfer->getAnnouncedDate());
		$this->assertNull($transfer->getContractDate());
		$this->assertEquals($message->getBody()->getMetadata()['type'], $transfer->getType());
		$this->assertTrue($transfer->isActive());
		$this->assertEquals(0, $transfer->getLike());
		$this->assertEquals(0, $transfer->getDislike());
		$this->assertNotNull($transfer->getSeason());
		/**
		 * Consume question message for get player info from player_service.
		 */
		$response = $this->brokerService->consumePureMessage([config('broker.queues.question')], 10);
		$this->assertNotEmpty($response);
		$playerMessage = json_decode(json_encode($response[0]), true);
		$payload = json_decode($playerMessage, true);
		$this->assertEquals(config('broker.services.team_name'), $payload['headers']['source']);
		$this->assertEquals(config('broker.services.player_name'), $payload['headers']['destination']);
		$this->assertEquals(PlayerWasTransferredProjectorListener::BROKER_EVENT_KEY, $payload['headers']['key']);
		$this->assertEquals(
			sprintf('%s#%s', $message->getBody()->getIdentifiers()['player'], $transfer->getStartDate()->format(DateTimeInterface::ATOM))
			, $payload['headers']['id']);
		$this->assertEquals(config('broker.services.player_name'), $payload['body']['entity']);
		$this->assertEquals($message->getBody()->getIdentifiers()['player'], $payload['body']['id']);
		/**
		 * Produce answer message from player service for update player info in transfer model.
		 */
		$answerMessage = (new CommandQueryMessage())
			->setHeaders(
				(new Headers())
					->setKey(PlayerWasTransferredProjectorListener::BROKER_EVENT_KEY)
					->setId($payload['headers']['id'])
					->setDestination(config('broker.services.team_name'))
					->setSource(config('broker.services.player_name'))
					->setDate(Carbon::now()->toDateTimeString())
			)->setBody([]);
		/**
		 * Handle answer message from player service for update player info in transfer model.
		 */
		app(PlayerWasTransferredUpdateInfo::class)->handle($answerMessage);
		/**
		 * Check player info is update in transfer model.
		 * @var Transfer $transfer
		 */
		$transfer = $this->transferRepository->findByPlayerId($message->getBody()->getIdentifiers()['player']);
		$this->assertNotEmpty($transfer);
		$transfer = $transfer[0];
		$this->assertNull($transfer->getPlayerName());
		$this->assertNull($transfer->getPlayerPosition());
	}

	protected function tearDown(): void
	{
		$this->brokerMessageCacheService->flush();
		$this->teamRepository->drop();
		$this->transferRepository->drop();
	}
}