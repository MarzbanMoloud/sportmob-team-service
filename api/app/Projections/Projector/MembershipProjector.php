<?php


namespace App\Projections\Projector;


use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Exceptions\Projection\ProjectionException;
use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Http\Services\Team\Traits\TeamTraits;
use App\Http\Services\Transfer\TransferService;
use App\Models\ReadModels\Transfer;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TransferRepository;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
use App\Services\Cache\Interfaces\TeamCacheServiceInterface;
use App\Services\Cache\Interfaces\TransferCacheServiceInterface;
use App\Services\Logger\Event;
use App\Traits\TransferLogicTrait;
use App\ValueObjects\Broker\Mediator\Message;
use Exception;
use Sentry\State\HubInterface;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class TransferProjector
 * @package App\Projections\Projector
 */
class MembershipProjector
{
	use TeamTraits, TransferLogicTrait;

	const TEAM_TYPE_CLUB = 'club';

	private TransferRepository $transferRepository;
	private TeamRepository $teamRepository;
	private TeamCacheServiceInterface $teamCacheService;
	private TransferService $transferService;
	private SerializerInterface $serializer;
	private TransferCacheServiceInterface $transferCacheService;
	private HubInterface $sentryHub;
	private BrokerMessageCacheServiceInterface $brokerMessageCacheService;
	private string $eventName;

	/**
	 * TransferProjector constructor.
	 * @param TransferRepository $transferRepository
	 * @param TeamRepository $teamRepository
	 * @param TeamCacheServiceInterface $teamCacheService
	 * @param TransferService $transferService
	 * @param TransferCacheServiceInterface $transferCacheService
	 * @param SerializerInterface $serializer
	 * @param HubInterface $sentryHub
	 * @param BrokerMessageCacheServiceInterface $brokerMessageCacheService
	 */
	public function __construct(
		TransferRepository $transferRepository,
		TeamRepository $teamRepository,
		TeamCacheServiceInterface $teamCacheService,
		TransferService $transferService,
		TransferCacheServiceInterface $transferCacheService,
		SerializerInterface $serializer,
		HubInterface $sentryHub,
		BrokerMessageCacheServiceInterface $brokerMessageCacheService
	) {
		$this->transferRepository = $transferRepository;
		$this->teamRepository = $teamRepository;
		$this->teamCacheService = $teamCacheService;
		$this->transferService = $transferService;
		$this->serializer = $serializer;
		$this->transferCacheService = $transferCacheService;
		$this->sentryHub = $sentryHub;
		$this->brokerMessageCacheService = $brokerMessageCacheService;
	}

	/**
	 * @param Message $message
	 * @throws ProjectionException
	 * @throws Exception
	 */
	public function applyMembershipWasUpdated(Message $message)
	{
		$this->eventName = config('mediator-event.events.membership_was_updated');
		Event::processing($message, $this->eventName);

		$body = $message->getBody();
		$metadata = $body->getMetadata();
		$identifier = $body->getIdentifiers();

		$this->checkIdentifierValidation($message);

		$this->checkMetadataValidation($message);

		$clubMemberships = [];

		foreach ($metadata['membership'] as $membership) {
			if (is_null($membership['dateFrom']) && is_null($membership['dateTo'])) {
				continue;
			}

			if ($membership['teamType'] != self::TEAM_TYPE_CLUB) {
				continue;
			}

			$clubMemberships[] = $membership;
		}

		$this->transformByPerson($message, $clubMemberships, $identifier['person'], $metadata['type']);

		//TODO:: create cache and ask question of playerService.

		Event::succeeded($message, $this->eventName);
	}

	/**
	 * @param Message $message
	 * @throws ProjectionException
	 */
	private function checkIdentifierValidation(Message $message): void
	{
		$requiredFields = ['person'];
		foreach ($requiredFields as $fieldName) {
			if (empty($message->getBody()->getIdentifiers()[$fieldName])) {
				$validationMessage = sprintf("%s field is empty.", ucfirst($fieldName));
				Event::failed($message, $this->eventName, $validationMessage);
				throw new ProjectionException($validationMessage,
					ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
			}
		}
	}

	/**
	 * @param Message $message
	 * @throws ProjectionException
	 */
	private function checkMetadataValidation(Message $message): void
	{
		$metadata = $message->getBody()->getMetadata();
		$requiredFields = ['membership', 'type'];
		foreach ($requiredFields as $fieldName) {
			if (empty($metadata[$fieldName])) {
				$validationMessage = sprintf("%s field is empty.", ucfirst($fieldName));
				Event::failed($message, $this->eventName, $validationMessage);
				throw new ProjectionException($validationMessage,
					ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
			}
		}
	}

	/**
	 * @param Transfer $transferModel
	 * @param Message $message
	 */
	private function persistTransfer(Transfer $transferModel, Message $message): void
	{
		try {
			$this->transferRepository->persist($transferModel);
		} catch (DynamoDBRepositoryException $exception) {
			Event::failed($message, $this->eventName, 'Failed to persist transfer.');
			$this->sentryHub->captureException($exception);
		}
	}
}