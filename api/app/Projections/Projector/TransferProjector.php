<?php


namespace App\Projections\Projector;


use App\Events\Projection\PlayerWasTransferredProjectorEvent;
use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Exceptions\Projection\ProjectionException;
use App\Exceptions\ReadModelValidatorException;
use App\Exceptions\ResourceNotFoundException;
use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Http\Services\Team\Traits\TeamTraits;
use App\Http\Services\Transfer\TransferService;
use App\Models\ReadModels\Transfer;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TransferRepository;
use App\Services\Cache\Interfaces\TeamCacheServiceInterface;
use App\Services\Cache\Interfaces\TransferCacheServiceInterface;
use App\Services\Cache\TransferCacheService;
use App\ValueObjects\Broker\Mediator\MessageBody;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
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
	private LoggerInterface $logger;
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
	 * @param LoggerInterface $logger
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		TransferRepository $transferRepository,
		TeamRepository $teamRepository,
		TeamCacheServiceInterface $teamCacheService,
		TransferService $transferService,
		TransferCacheServiceInterface $transferCacheService,
		LoggerInterface $logger,
		SerializerInterface $serializer
	) {
		$this->transferRepository = $transferRepository;
		$this->teamRepository = $teamRepository;
		$this->teamCacheService = $teamCacheService;
		$this->transferService = $transferService;
		$this->logger = $logger;
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
		$this->logger->alert(
			sprintf("%s handler in progress.", $this->eventName),
			$this->serializer->normalize($body, 'array')
		);
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
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'fromTeamId and toTeamId could not be null at same time.'
				), $this->serializer->normalize($body, 'array')
			);
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
		$this->logger->alert(
			sprintf("%s handler completed successfully.", $this->eventName),
			$this->serializer->normalize($transferModel, 'array')
		);
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
				$this->logger->alert(
					sprintf(
						"%s handler failed because of %s",
						$this->eventName,
						sprintf("%s field is empty.", $prettyFieldName)
					), $this->serializer->normalize($body, 'array')
				);
				throw new ProjectionException(
					sprintf("%s field is empty.", $prettyFieldName),
					ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR
				);
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
				$this->logger->alert(
					sprintf(
						"%s handler failed because of %s",
						$this->eventName,
						sprintf("%s field is empty.", $prettyFieldName)
					), $this->serializer->normalize($body, 'array')
				);
				throw new ProjectionException(
					sprintf("%s field is empty.", $prettyFieldName),
					ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR
				);
			}
		}
		if (is_null($metadata['active'])) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'Active field is empty.'
				), $this->serializer->normalize($body, 'array')
			);
			throw new ProjectionException(
				'Active field is empty.',
				ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR
			);
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
					$this->logger->alert(
						sprintf(
							"%s handler failed because of %s",
							$this->eventName,
							sprintf('Could not find team by given Id :: %s', $identifier[$field])
						), $identifier
					);
					throw new ProjectionException(sprintf('Could not find team by given Id :: %s',
						$identifier[$field]), ResponseServiceInterface::STATUS_CODE_VALIDATION_ERROR, $exception);
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
			->setType($metadata['type']);

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
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'Failed to persist transfer.'
				), $this->serializer->normalize($transferModel, 'array')
			);
			throw new ProjectionException('Failed to persist transfer.', $exception->getCode(), $exception);
		}
	}
}