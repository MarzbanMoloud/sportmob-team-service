<?php


namespace App\Listeners\Projection;


use App\Events\Projection\PlayerWasTransferredProjectorEvent;
use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Exceptions\Projection\ProjectionException;
use App\Listeners\Traits\PlayerWasTransferredNotificationTrait;
use App\Models\Repositories\TransferRepository;
use App\Services\BrokerInterface;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
use App\Services\Logger\Event;
use App\ValueObjects\Broker\CommandQuery\Headers;
use App\ValueObjects\Broker\CommandQuery\Message;
use Carbon\Carbon;
use DateTimeInterface;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class PlayerWasTransferredProjectorListener
 * @package App\Listeners\Projection
 */
class PlayerWasTransferredProjectorListener
{
	use PlayerWasTransferredNotificationTrait;

	const BROKER_EVENT_KEY = 'PlayerWasTransferredUpdateInfo';
	const BROKER_NOTIFICATION_KEY = 'transfer-player';

	private BrokerInterface $broker;
	private SerializerInterface $serializer;
	private BrokerMessageCacheServiceInterface $brokerMessageCacheService;
	private TransferRepository $transferRepository;

	/**
	 * PlayerWasTransferredProjectorListener constructor.
	 * @param BrokerInterface $broker
	 * @param BrokerMessageCacheServiceInterface $brokerMessageCacheService
	 * @param TransferRepository $transferRepository
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		BrokerInterface $broker,
		BrokerMessageCacheServiceInterface $brokerMessageCacheService,
		TransferRepository $transferRepository,
		SerializerInterface $serializer
	) {
		$this->serializer = $serializer;
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
		$eventName = config('mediator-event.events.player_was_transferred');
		if (! $this->brokerMessageCacheService->hasPlayerInfo($event->transfer->getPlayerId())) {
			$message = (new Message())
				->setHeaders(
					(new Headers())
						->setEventId($event->mediatorMessage->getHeaders()->getId())
						->setKey(self::BROKER_EVENT_KEY)
						->setId($event->transfer->getId())
						->setDestination(config('broker.services.player_name'))
						->setSource(config('broker.services.team_name'))
						->setDate(Carbon::now()->format('c'))
				)->setBody([
					'entity' => config('broker.services.player_name'),
					'id' => $event->transfer->getPlayerId()
				]);
			$this->broker->flushMessages()->addMessage(
				self::BROKER_EVENT_KEY,
				$this->serializer->serialize($message, 'json')
			)->produceMessage(config('broker.topics.question_player'));
			Event::needToAsk($message, $eventName, self::BROKER_EVENT_KEY, config('broker.services.player_name'));
			return;
		}
		$playerInfo = $this->brokerMessageCacheService->getPlayerInfo($event->transfer->getPlayerId());
		$event->transfer
			->setPlayerName($playerInfo['fullName'] ?? $playerInfo['shortName'])
			->setPlayerPosition($playerInfo['position']);
		try {
			$this->transferRepository->persist($event->transfer);
		} catch (DynamoDBRepositoryException $exception) {
			$validationMessage = 'Failed to update transfer.';
			Event::failed($event->mediatorMessage, $eventName, $validationMessage);
			throw new ProjectionException($validationMessage, $exception->getCode());
		}
		if (strpos($event->transfer->getSeason(), date('Y')) != false) {
			$this->sendNotification($event->transfer, self::BROKER_NOTIFICATION_KEY);
		}
	}
}