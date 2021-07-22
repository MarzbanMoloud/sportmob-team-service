<?php


namespace App\Projections\Projector;


use App\Events\Projection\PlayerWasTransferredProjectorEvent;
use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Exceptions\Projection\ProjectionException;
use App\Exceptions\ReadModelValidatorException;
use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Http\Services\Team\Traits\TeamTraits;
use App\Http\Services\Transfer\TransferService;
use App\Models\ReadModels\Transfer;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TransferRepository;
use App\Services\Cache\Interfaces\TeamCacheServiceInterface;
use App\Services\Cache\Interfaces\TransferCacheServiceInterface;
use App\Services\Cache\TransferCacheService;
use App\Services\Logger\Event;
use App\ValueObjects\Broker\Mediator\Message;
use DateTime;
use DateTimeImmutable;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class TransferProjector
 * @package App\Projections\Projector
 */
class TransferProjector
{
	use TeamTraits;

	private TransferRepository $transferRepository;
	private TeamRepository $teamRepository;
	private TeamCacheServiceInterface $teamCacheService;
	private TransferService $transferService;
	private SerializerInterface $serializer;
	private TransferCacheServiceInterface $transferCacheService;
	private string $eventName;

	/**
	 * TransferProjector constructor.
	 * @param TransferRepository $transferRepository
	 * @param TeamRepository $teamRepository
	 * @param TeamCacheServiceInterface $teamCacheService
	 * @param TransferService $transferService
	 * @param TransferCacheServiceInterface $transferCacheService
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		TransferRepository $transferRepository,
		TeamRepository $teamRepository,
		TeamCacheServiceInterface $teamCacheService,
		TransferService $transferService,
		TransferCacheServiceInterface $transferCacheService,
		SerializerInterface $serializer
	) {
		$this->transferRepository = $transferRepository;
		$this->teamRepository = $teamRepository;
		$this->teamCacheService = $teamCacheService;
		$this->transferService = $transferService;
		$this->serializer = $serializer;
		$this->transferCacheService = $transferCacheService;
	}

	/**
	 * @param Message $message
	 * @throws ProjectionException
	 */
	public function applyPlayerWasTransferred(Message $message)
	{
		$body = $message->getBody();
		$identifier = $body->getIdentifiers();
		$metadata = $body->getMetadata();
		$this->eventName = config('mediator-event.events.player_was_transferred');
		Event::processing($message, $this->eventName);
		$this->checkIdentifierValidation($message);
		$this->checkMetadataValidation($message);
		if ($metadata['active'] == true) {
			if ($latestTransfers = $this->transferRepository->findActiveTransfer($identifier['player'])) {
				$this->inactiveLastActiveTransferByPlayerId($latestTransfers[0], $message);
			}
		}
		$transferModel = $this->createTransferModel($identifier, $metadata);
		try {
			$transferModel->prePersist();
		} catch (ReadModelValidatorException $e) {
			Event::failed($message, $this->eventName, 'fromTeamId and toTeamId could not be null at same time.');
		}
		$this->persistTransfer($transferModel, $message);
		event(new PlayerWasTransferredProjectorEvent($transferModel, $message));
		//TODO:: create cache.
		Event::succeeded($message, $this->eventName);
	}

	/**
	 * @param Message $message
	 * @throws ProjectionException
	 */
	private function checkIdentifierValidation(Message $message): void
	{
		$requiredFields = ['player', 'to', 'transfer'];
		foreach ($requiredFields as $fieldName) {
			if (empty($message->getBody()->getIdentifiers()[$fieldName])) {
				$validationMessage = sprintf("%s field is empty.", ucfirst($fieldName));
				Event::failed($message, $this->eventName, $validationMessage);
				throw new ProjectionException($validationMessage, ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
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
		$requiredFields = ['type'];
		foreach ($requiredFields as $fieldName) {
			if (empty($metadata[$fieldName])) {
				$validationMessage = sprintf("%s field is empty.", ucfirst($fieldName));
				Event::failed($message, $this->eventName, $validationMessage);
				throw new ProjectionException($validationMessage, ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
			}
		}
		if (is_null($metadata['active'])) {
			$validationMessage = 'Active field is empty.';
			Event::failed($message, $this->eventName, $validationMessage);
			throw new ProjectionException($validationMessage, ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
		}
	}

	/**
	 * @param array $identifier
	 * @return array
	 * @throws ProjectionException
	 */
	private function getTeamsName(array $identifier): array
	{
		$teamsName = [];
		foreach (['from', 'to'] as $field) {
			if ($identifier[$field]) {
				try {
					$teamsName[$field] = $this->findTeam($identifier[$field])->getName()->getOriginal();
				} catch (\Throwable $exception) {
					$message = sprintf('Could not find team by given Id :: %s', $identifier[$field]);
					Event::failed($identifier, $this->eventName, $message);
					throw new ProjectionException($message, ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR, $exception);
				}
			}
		}
		return $teamsName;
	}

	/**
	 * @param Transfer $latestTransfer
	 * @param Message $message
	 * @throws ProjectionException
	 */
	private function inactiveLastActiveTransferByPlayerId(Transfer $latestTransfer, Message $message): void
	{
		$latestTransfer
			->setActive(false)
			->setEndDate($latestTransfer->getEndDate() ?: new DateTimeImmutable($message->getBody()->getMetadata()['startDate']));
		$this->persistTransfer($latestTransfer, $message);
	}

	/**
	 * @param array $identifier
	 * @param array $metadata
	 * @return Transfer
	 * @throws \Exception
	 */
	private function createTransferModel(array $identifier, array $metadata): Transfer
	{
		$transferModel = (new Transfer())
			->setId($identifier['transfer'])
			->setPlayerId($identifier['player'])
			->setFromTeamId($identifier['from'])
			->setToTeamId($identifier['to'])
			->setStartDate($metadata['startDate'] ? new DateTimeImmutable($metadata['startDate']) : Transfer::getDateTimeImmutable())
			->setEndDate($metadata['endDate'] ? new DateTimeImmutable($metadata['endDate']) : null)
			->setActive($metadata['active'])
			->setType($metadata['type'])
			->setCreatedAt(new DateTime());

		$teamsName = $this->getTeamsName($identifier);
		if (! empty($teamsName['from'])) {
			$transferModel->setFromTeamName($teamsName['from']);
		}
		if (! empty( $teamsName['to'])) {
			$transferModel->setToTeamName($teamsName['to']);
		}
		return $transferModel;
	}

	/**
	 * @param Transfer $transferModel
	 * @param Message $message
	 * @throws ProjectionException
	 */
	private function persistTransfer(Transfer $transferModel, Message $message): void
	{
		try {
			$this->transferRepository->persist($transferModel);
		} catch (DynamoDBRepositoryException $exception) {
			$validationMessage = 'Failed to persist transfer.';
			Event::failed($message, $this->eventName, $validationMessage);
			throw new ProjectionException($validationMessage, $exception->getCode(), $exception);
		}
	}
}