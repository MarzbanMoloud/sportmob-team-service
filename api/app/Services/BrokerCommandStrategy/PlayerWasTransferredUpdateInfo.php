<?php


namespace App\Services\BrokerCommandStrategy;


use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Listeners\Projection\PlayerWasTransferredProjectorListener;
use App\Listeners\Traits\PlayerWasTransferredNotificationTrait;
use App\Models\ReadModels\Transfer;
use App\Models\Repositories\TransferRepository;
use App\Services\BrokerCommandStrategy\Interfaces\BrokerCommandEventInterface;
use App\Services\BrokerInterface;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
use App\ValueObjects\Broker\CommandQuery\Headers;
use App\ValueObjects\Broker\CommandQuery\Message;
use App\ValueObjects\Broker\Notification\Body as NotificationBody;
use App\ValueObjects\Broker\Notification\Headers as NotificationHeaders;
use App\ValueObjects\Broker\Notification\Message as NotificationMessage;
use DateTimeImmutable;
use Sentry\State\HubInterface;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class PlayerWasTransferredUpdateInfo
 * @package App\Services\BrokerCommandStrategy
 */
class PlayerWasTransferredUpdateInfo implements BrokerCommandEventInterface
{
	use PlayerWasTransferredNotificationTrait;

	private TransferRepository $transferRepository;
	private BrokerMessageCacheServiceInterface $brokerMessageCacheService;
	private HubInterface $sentryHub;
	private SerializerInterface $serializer;
	private BrokerInterface $broker;

	/**
	 * PlayerWasTransferredUpdateInfo constructor.
	 * @param TransferRepository $transferRepository
	 * @param BrokerMessageCacheServiceInterface $brokerMessageCacheService
	 * @param HubInterface $sentryHub
	 * @param BrokerInterface $broker
	 */
	public function __construct(
		TransferRepository $transferRepository,
		BrokerMessageCacheServiceInterface $brokerMessageCacheService,
		HubInterface $sentryHub,
		BrokerInterface $broker
	) {
		$this->transferRepository = $transferRepository;
		$this->brokerMessageCacheService = $brokerMessageCacheService;
		$this->sentryHub = $sentryHub;
		$this->serializer = app('Serializer');
		$this->broker = $broker;
	}

	/**
	 * @param Headers $headers
	 * @return bool
	 */
	public function support(Headers $headers): bool
	{
		return
			($headers->getDestination() == config('broker.services.team_name')) &&
			($headers->getKey() == PlayerWasTransferredProjectorListener::BROKER_EVENT_KEY);
	}

	/**
	 * @param Message $commandQuery
	 */
	public function handle(Message $commandQuery): void
	{
		if (empty($commandQuery->getBody())) {
			return;
		}
		$playerId = $commandQuery->getHeaders()->getId();
		$playerTransfers = $this->transferRepository->findByPlayerId($playerId);
		foreach ($playerTransfers as $transfer) {
			/**
			 * @var Transfer $transfer
			 */
			$transfer
				->setPlayerName($commandQuery->getBody()['fullName'] ?? $commandQuery->getBody()['shortName'])
				->setPlayerPosition($commandQuery->getBody()['position']);
			try {
				$this->transferRepository->persist($transfer);
			} catch (DynamoDBRepositoryException $exception) {
				$this->sentryHub->captureException($exception);
			}
		}
		/**
		 * Put playerInfo in cache.
		 */
		$data = $commandQuery->getBody();
		unset($data['entity']);
		$this->brokerMessageCacheService->putPlayerInfo($data);
		/**
		 * Notification message.
		 * @var Transfer $activeTransfer
		 */
		$activeTransfer = $this->transferRepository->findActiveTransfer($playerId);
		if (!$activeTransfer) {
			return;
		}
		$this->sendNotification($activeTransfer[0], PlayerWasTransferredProjectorListener::BROKER_NOTIFICATION_KEY);
	}
}