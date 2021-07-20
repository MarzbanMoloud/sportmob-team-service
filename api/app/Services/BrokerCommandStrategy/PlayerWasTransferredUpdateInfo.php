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
use App\Services\Logger\Answer;
use App\ValueObjects\Broker\CommandQuery\Message;
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
	 */
	public function __construct(
		TransferRepository $transferRepository,
		TransferCacheServiceInterface $transferCacheService,
		TransferService $transferService,
		BrokerMessageCacheServiceInterface $brokerMessageCacheService,
		HubInterface $sentryHub,
		BrokerInterface $broker,
		SerializerInterface $serializer
	) {
		$this->transferRepository = $transferRepository;
		$this->brokerMessageCacheService = $brokerMessageCacheService;
		$this->sentryHub = $sentryHub;
		$this->serializer = $serializer;
		$this->broker = $broker;
		$this->transferCacheService = $transferCacheService;
		$this->transferService = $transferService;
	}

	/**
	 * @param Message $commandQuery
	 */
	public function handle(Message $commandQuery): void
	{
		Answer::handled($commandQuery, PlayerWasTransferredProjectorListener::BROKER_EVENT_KEY, $commandQuery->getHeaders()->getSource(), __CLASS__);
		Answer::processing($commandQuery, PlayerWasTransferredProjectorListener::BROKER_EVENT_KEY);

		if (empty($commandQuery->getBody())) {
			Answer::failed($commandQuery, PlayerWasTransferredProjectorListener::BROKER_EVENT_KEY, 'Data not found.');
			return;
		}

		/** @var Transfer $transfer */
		$transfer = $this->transferRepository->find(['id' => $commandQuery->getHeaders()->getId()]);

		$transfer
			->setPlayerName($commandQuery->getBody()['fullName'] ?? $commandQuery->getBody()['shortName'])
			->setPlayerPosition($commandQuery->getBody()['position']);

		try {
			$this->transferRepository->persist($transfer);
		} catch (DynamoDBRepositoryException $exception) {
			Answer::failed($transfer, PlayerWasTransferredProjectorListener::BROKER_EVENT_KEY, 'Failed to persist transfer.');
			$this->sentryHub->captureException($exception);
		}
		//TODO:: create cache.

		/** Put playerInfo in cache.*/
		$data = $commandQuery->getBody();
		unset($data['entity']);
		$this->brokerMessageCacheService->putPlayerInfo($data);

		/**
		 * Notification message.
		 * @var Transfer $activeTransfer
		 */
		if (strpos($transfer->getSeason(), date('Y')) != false) {
			$this->sendNotification($transfer, PlayerWasTransferredProjectorListener::BROKER_NOTIFICATION_KEY);
		}

		Answer::succeeded($commandQuery, PlayerWasTransferredProjectorListener::BROKER_EVENT_KEY);
	}
}