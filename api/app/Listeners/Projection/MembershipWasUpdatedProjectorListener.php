<?php


namespace App\Listeners\Projection;


use App\Events\Projection\MembershipWasUpdatedProjectorEvent;
use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Listeners\Traits\PlayerWasTransferredNotificationTrait;
use App\Models\Repositories\TransferRepository;
use App\Services\BrokerInterface;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
use App\Services\Logger\Event;
use App\ValueObjects\Broker\CommandQuery\Headers;
use App\ValueObjects\Broker\CommandQuery\Message;
use Carbon\Carbon;
use Sentry\State\HubInterface;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class MembershipWasUpdatedProjectorListener
 * @package App\Listeners\Projection
 */
class MembershipWasUpdatedProjectorListener
{
	use PlayerWasTransferredNotificationTrait;

	const BROKER_EVENT_KEY = 'MembershipWasUpdated-UpdateInfo';
	const BROKER_NOTIFICATION_KEY = 'membership-person';

	private BrokerInterface $broker;
	private SerializerInterface $serializer;
	private BrokerMessageCacheServiceInterface $brokerMessageCacheService;
	private TransferRepository $transferRepository;
	private HubInterface $sentryHub;

	/**
	 * PlayerWasTransferredProjectorListener constructor.
	 * @param BrokerInterface $broker
	 * @param BrokerMessageCacheServiceInterface $brokerMessageCacheService
	 * @param TransferRepository $transferRepository
	 * @param SerializerInterface $serializer
	 * @param HubInterface $sentryHub
	 */
	public function __construct(
		BrokerInterface $broker,
		BrokerMessageCacheServiceInterface $brokerMessageCacheService,
		TransferRepository $transferRepository,
		SerializerInterface $serializer,
		HubInterface $sentryHub
	) {
		$this->serializer = $serializer;
		$this->broker = $broker;
		$this->brokerMessageCacheService = $brokerMessageCacheService;
		$this->transferRepository = $transferRepository;
		$this->sentryHub = $sentryHub;
	}

	/**
	 * @param MembershipWasUpdatedProjectorEvent $event
	 */
	public function handle(MembershipWasUpdatedProjectorEvent $event)
	{
		$eventName = config('mediator-event.events.membership_was_updated');

		if (! $this->brokerMessageCacheService->hasPlayerInfo($event->transfer->getPersonId())) {

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
					'id' => $event->transfer->getPersonId()
				]);

			$this->broker->flushMessages()->addMessage(
				self::BROKER_EVENT_KEY,
				$this->serializer->serialize($message, 'json')
			)->produceMessage(config('broker.topics.question_player'));

			Event::needToAsk($message, $eventName, self::BROKER_EVENT_KEY, config('broker.services.player_name'));

			return;
		}

		$playerInfo = $this->brokerMessageCacheService->getPlayerInfo($event->transfer->getPersonId());
		$event->transfer->setPersonName($playerInfo['fullName'] ?? $playerInfo['shortName']);

		try {
			$this->transferRepository->persist($event->transfer);
		} catch (DynamoDBRepositoryException $exception) {
			Event::failed($event->mediatorMessage, $eventName, 'Failed to update transfer.');
			$this->sentryHub->captureException($exception);
		}

		//TODO:: notification
		/*if (strpos($event->transfer->getSeason(), date('Y')) != false) {
			$this->sendNotification($event->transfer, self::BROKER_NOTIFICATION_KEY);
		}*/
	}
}