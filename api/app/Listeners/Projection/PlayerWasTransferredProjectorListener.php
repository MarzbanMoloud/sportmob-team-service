<?php


namespace App\Listeners\Projection;


use App\Events\Projection\PlayerWasTransferredProjectorEvent;
use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Exceptions\Projection\ProjectionException;
use App\Listeners\Traits\PlayerWasTransferredNotificationTrait;
use App\Models\Repositories\TransferRepository;
use App\Services\BrokerInterface;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
use App\ValueObjects\Broker\CommandQuery\Headers;
use App\ValueObjects\Broker\CommandQuery\Message;
use App\ValueObjects\Broker\Notification\Message as NotificationMessage;
use App\ValueObjects\Broker\Notification\Headers as NotificationHeaders;
use App\ValueObjects\Broker\Notification\Body as NotificationBody;
use Carbon\Carbon;
use DateTimeImmutable;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class PlayerWasTransferredProjectorListener
 * @package App\Listeners\Projection
 */
class PlayerWasTransferredProjectorListener
{
	use PlayerWasTransferredNotificationTrait;

	const BROKER_EVENT_KEY = 'PlayerWasTransferredUpdateInfo';
	const BROKER_NOTIFICATION_KEY = 'PlayerWasTransferredNotification';

	private BrokerInterface $broker;
	private SerializerInterface $serializer;
	private BrokerMessageCacheServiceInterface $brokerMessageCacheService;
	private TransferRepository $transferRepository;

	/**
	 * PlayerWasTransferredProjectorListener constructor.
	 * @param BrokerInterface $broker
	 * @param BrokerMessageCacheServiceInterface $brokerMessageCacheService
	 * @param TransferRepository $transferRepository
	 */
	public function __construct(
		BrokerInterface $broker,
		BrokerMessageCacheServiceInterface $brokerMessageCacheService,
		TransferRepository $transferRepository
	) {
		$this->serializer = app('Serializer');
		$this->broker = $broker;
		$this->brokerMessageCacheService = $brokerMessageCacheService;
		$this->transferRepository = $transferRepository;
	}

	/**
	 * @param PlayerWasTransferredProjectorEvent $event
	 * @throws ProjectionException
	 */
	public function handle(PlayerWasTransferredProjectorEvent $event)
	{
		if (! $this->brokerMessageCacheService->hasPlayerInfo($event->transfer->getPlayerId())) {
			$message = (new Message())
				->setHeaders(
					(new Headers())
						->setKey(self::BROKER_EVENT_KEY)
						->setId($event->transfer->getPlayerId())
						->setDestination(config('broker.services.player_name'))
						->setSource(config('broker.services.team_name'))
						->setDate(Carbon::now()->toDateTimeString())
				)->setBody([
					'entity' => config('broker.services.player_name'),
					'id' => $event->transfer->getPlayerId()
				]);
			$this->broker->flushMessages()->addMessage(
				self::BROKER_EVENT_KEY,
				$this->serializer->serialize($message, 'json')
			)->produceMessage(config('broker.topics.question'));
			return;
		}
		$playerInfo = $this->brokerMessageCacheService->getPlayerInfo($event->transfer->getPlayerId());
		$event->transfer
			->setPlayerName($playerInfo['fullName'] ?? $playerInfo['shortName'])
			->setPlayerPosition($playerInfo['position']);
		try {
			$this->transferRepository->persist($event->transfer);
		} catch (DynamoDBRepositoryException $exception) {
			throw new ProjectionException('Failed to update transfer.', $exception->getCode());
		}
		$this->sendNotification($event->transfer, self::BROKER_NOTIFICATION_KEY);
	}
}