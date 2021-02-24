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
use Carbon\Carbon;
use DateTimeInterface;
use Psr\Log\LoggerInterface;
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
	private LoggerInterface $logger;

	/**
	 * PlayerWasTransferredProjectorListener constructor.
	 * @param BrokerInterface $broker
	 * @param BrokerMessageCacheServiceInterface $brokerMessageCacheService
	 * @param TransferRepository $transferRepository
	 * @param SerializerInterface $serializer
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		BrokerInterface $broker,
		BrokerMessageCacheServiceInterface $brokerMessageCacheService,
		TransferRepository $transferRepository,
		SerializerInterface $serializer,
		LoggerInterface $logger
	) {
		$this->serializer = $serializer;
		$this->broker = $broker;
		$this->brokerMessageCacheService = $brokerMessageCacheService;
		$this->transferRepository = $transferRepository;
		$this->logger = $logger;
	}

	/**
	 * @param PlayerWasTransferredProjectorEvent $event
	 * @throws ProjectionException
	 */
	public function handle(PlayerWasTransferredProjectorEvent $event)
	{
		$eventName = config('mediator-event.events.player_was_transferred');
		if (! $this->brokerMessageCacheService->hasPlayerInfo($event->transfer->getPlayerId())) {
			$message = (new Message())
				->setHeaders(
					(new Headers())
						->setKey(self::BROKER_EVENT_KEY)
						->setId(
							sprintf('%s#%s', $event->transfer->getPlayerId(),
								$event->transfer->getStartDate()->format(DateTimeInterface::ATOM))
						)
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

			$this->logger->alert(
				sprintf(
					"%s handler needs to ask %s from %s",
					$eventName,
					self::BROKER_EVENT_KEY,
					config('broker.services.player_name')
				),
				$this->serializer->normalize($message, 'array')
			);
			return;
		}
		$playerInfo = $this->brokerMessageCacheService->getPlayerInfo($event->transfer->getPlayerId());
		$event->transfer
			->setPlayerName($playerInfo['fullName'] ?? $playerInfo['shortName'])
			->setPlayerPosition($playerInfo['position']);
		try {
			$this->transferRepository->persist($event->transfer);
		} catch (DynamoDBRepositoryException $exception) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$eventName,
					'Failed to update transfer.'
				), $this->serializer->normalize($event->transfer, 'array')
			);
			throw new ProjectionException('Failed to update transfer.', $exception->getCode());
		}
		$this->sendNotification($event->transfer, self::BROKER_NOTIFICATION_KEY); //TODO:: Notification
	}
}