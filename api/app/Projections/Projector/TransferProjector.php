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
use App\ValueObjects\Broker\Mediator\MessageBody;
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
	 * @param MessageBody $body
	 * @throws ProjectionException
	 */
	public function applyPlayerWasTransferred(MessageBody $body)
	{
		$this->eventName = config('mediator-event.events.player_was_transferred');
		Event::processing($body, $this->eventName);
		$identifier = $body->getIdentifiers();
		$metadata = $body->getMetadata();
		$this->checkIdentifierValidation($body);
		$this->checkMetadataValidation($body);
		if ($metadata['active'] == true) {
			if ($latestTransfers = $this->transferRepository->findActiveTransfer($identifier['player'])) {
				$this->inactiveLastActiveTransferByPlayerId($latestTransfers[0], $metadata);
			}
		}
		$transferModel = $this->createTransferModel($identifier, $metadata);
		try {
			$transferModel->prePersist();
		} catch (ReadModelValidatorException $e) {
			Event::failed($body, $this->eventName, 'fromTeamId and toTeamId could not be null at same time.');
		}
		$this->persistTransfer($transferModel);
		event(new PlayerWasTransferredProjectorEvent($transferModel));
		/** Create cache by call service */
		try {
			$this->transferCacheService->forget('transfer_by_team*');
			$this->transferCacheService->forget(TransferCacheService::getTransferByPlayerKey($identifier['player']));
			$this->transferService->listByPlayer($identifier['player']);
			$this->transferService->listByTeam($identifier['to'], $transferModel->getSeason());
		} catch (\Exception $exception) {
		}
		Event::succeeded($transferModel, $this->eventName);
	}

	/**
	 * @param MessageBody $body
	 * @throws ProjectionException
	 */
	private function checkIdentifierValidation(MessageBody $body): void
	{
		$requiredFields = [
			'player' => 'Player',
			'to' => 'To',
		];
		foreach ($requiredFields as $fieldName => $prettyFieldName) {
			if (empty($body->getIdentifiers()[$fieldName])) {
				$message = sprintf("%s field is empty.", $prettyFieldName);
				Event::failed($body, $this->eventName, $message);
				throw new ProjectionException($message, ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
			}
		}
	}

	/**
	 * @param MessageBody $body
	 * @throws ProjectionException
	 */
	private function checkMetadataValidation(MessageBody $body): void
	{
		$metadata = $body->getMetadata();
		$requiredFields = [
			'startDate' => 'Start Date',
			'type' => 'Type',
		];
		foreach ($requiredFields as $fieldName => $prettyFieldName) {
			if (empty($metadata[$fieldName])) {
				$message = sprintf("%s field is empty.", $prettyFieldName);
				Event::failed($body, $this->eventName, $message);
				throw new ProjectionException($message, ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
			}
		}
		if (is_null($metadata['active'])) {
			$message = 'Active field is empty.';
			Event::failed($body, $this->eventName, $message);
			throw new ProjectionException($message, ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR);
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
	 * @param array $metadata
	 * @throws ProjectionException
	 */
	private function inactiveLastActiveTransferByPlayerId(Transfer $latestTransfer, array $metadata): void
	{
		$latestTransfer->setActive( false )
			->setEndDate( $latestTransfer->getEndDate() ?:
				new DateTimeImmutable( $metadata[ 'startDate' ] ) );
		$this->persistTransfer($latestTransfer);
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
			->setPlayerId($identifier['player'])
			->setFromTeamId($identifier['from'])
			->setToTeamId($identifier['to'])
			->setStartDate(new DateTimeImmutable($metadata['startDate']))
			->setEndDate($metadata['endDate' ] ? new DateTimeImmutable($metadata['endDate']) : null)
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
	 * @throws ProjectionException
	 */
	private function persistTransfer(Transfer $transferModel): void
	{
		try {
			$this->transferRepository->persist($transferModel);
		} catch (DynamoDBRepositoryException $exception) {
			$message = 'Failed to persist transfer.';
			Event::failed($transferModel, $this->eventName, $message);
			throw new ProjectionException($message, $exception->getCode(), $exception);
		}
	}
}