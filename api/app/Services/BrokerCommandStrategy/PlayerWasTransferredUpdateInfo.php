<?php


namespace App\Services\BrokerCommandStrategy;


use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Http\Services\Transfer\TransferService;
use App\Listeners\Projection\PlayerWasTransferredProjectorListener;
use App\Listeners\Traits\PlayerWasTransferredNotificationTrait;
use App\Models\ReadModels\Transfer;
use App\Models\Repositories\TransferRepository;
use App\Services\BrokerCommandStrategy\Interfaces\BrokerCommandEventInterface;
use App\Services\BrokerInterface;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
use App\Services\Cache\Interfaces\TransferCacheServiceInterface;
use App\ValueObjects\Broker\CommandQuery\Headers;
use App\ValueObjects\Broker\CommandQuery\Message;
use Psr\Log\LoggerInterface;
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
	private LoggerInterface $logger;
	private TransferCacheServiceInterface $transferCacheService;
	private TransferService $transferService;

	/**
	 * PlayerWasTransferredUpdateInfo constructor.
	 * @param TransferRepository $transferRepository
	 * @param TransferCacheServiceInterface $transferCacheService
	 * @param TransferService $transferService
	 * @param BrokerMessageCacheServiceInterface $brokerMessageCacheService
	 * @param HubInterface $sentryHub
	 * @param BrokerInterface $broker
	 * @param SerializerInterface $serializer
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		TransferRepository $transferRepository,
		TransferCacheServiceInterface $transferCacheService,
		TransferService $transferService,
		BrokerMessageCacheServiceInterface $brokerMessageCacheService,
		HubInterface $sentryHub,
		BrokerInterface $broker,
		SerializerInterface $serializer,
		LoggerInterface $logger
	) {
		$this->transferRepository = $transferRepository;
		$this->brokerMessageCacheService = $brokerMessageCacheService;
		$this->sentryHub = $sentryHub;
		$this->serializer = $serializer;
		$this->broker = $broker;
		$this->logger = $logger;
		$this->transferCacheService = $transferCacheService;
		$this->transferService = $transferService;
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
		$this->logger->alert(
			sprintf(
				"Answer %s by %s will handle by %s.",
				PlayerWasTransferredProjectorListener::BROKER_EVENT_KEY,
				$commandQuery->getHeaders()->getSource(),
				__CLASS__
			),
			$this->serializer->normalize($commandQuery, 'array')
		);
		$this->logger->alert(
			sprintf("%s handler in progress.", PlayerWasTransferredProjectorListener::BROKER_EVENT_KEY),
			$this->serializer->normalize($commandQuery, 'array')
		);
		if (empty($commandQuery->getBody())) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s.",
					PlayerWasTransferredProjectorListener::BROKER_EVENT_KEY,
					'Data not found.'
				),
				$this->serializer->normalize($commandQuery, 'array')
			);
			return;
		}
		[$playerId, $startDate] = explode('#', $commandQuery->getHeaders()->getId());
		/** @var Transfer $transfer */
		$transfer = $this->transferRepository->find(['playerId' => $playerId, 'startDate' => $startDate]);
		$transfer->setPlayerName($commandQuery->getBody()['fullName'] ?? $commandQuery->getBody()['shortName'])
			->setPlayerPosition($commandQuery->getBody()['position']);
		try {
			$this->transferRepository->persist($transfer);
		} catch (DynamoDBRepositoryException $exception) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s.",
					PlayerWasTransferredProjectorListener::BROKER_EVENT_KEY,
					'Failed to persist transfer.'
				),
				$this->serializer->normalize($transfer, 'array')
			);
			$this->sentryHub->captureException($exception);
		}
		try {
			$this->transferCacheService->forget('transfer_by_*');//per playerId and teamId
			$this->transferService->listByPlayer($playerId);
		} catch (\Exception $e) {
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
			goto successfullyLog;
		}
		$this->sendNotification($activeTransfer[0], PlayerWasTransferredProjectorListener::BROKER_NOTIFICATION_KEY);//TODO:: notification
		successfullyLog:
		$this->logger->alert(
			sprintf("%s handler completed successfully.", PlayerWasTransferredProjectorListener::BROKER_EVENT_KEY),
			$this->serializer->normalize($transfer, 'array')
		);
	}
}