<?php


namespace App\Projections\Projector;


use App\Events\Projection\PlayerWasTransferredProjectorEvent;
use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Exceptions\Projection\ProjectionException;
use App\Http\Services\Response\Interfaces\ResponseServiceInterface;
use App\Http\Services\Team\Traits\TeamTraits;
use App\Models\ReadModels\Transfer;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TransferRepository;
use App\Services\Cache\Interfaces\TeamCacheServiceInterface;
use App\ValueObjects\Broker\Mediator\MessageBody;
use DateTimeImmutable;


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

	/**
	 * TransferProjector constructor.
	 * @param TransferRepository $transferRepository
	 * @param TeamRepository $teamRepository
	 * @param TeamCacheServiceInterface $teamCacheService
	 */
	public function __construct(
		TransferRepository $transferRepository,
		TeamRepository $teamRepository,
		TeamCacheServiceInterface $teamCacheService
	) {
		$this->transferRepository = $transferRepository;
		$this->teamRepository = $teamRepository;
		$this->teamCacheService = $teamCacheService;
	}

	/**
	 * @param MessageBody $body
	 * @throws ProjectionException
	 */
	public function applyPlayerWasTransferred(MessageBody $body)
	{
		$identifier = $body->getIdentifiers();
		$metadata = $body->getMetadata();
		if (empty( $identifier[ 'player' ] )) {
			throw new ProjectionException( 'Empty or invalid player ID' );
		}
		if (empty( $identifier[ 'to' ] )) {
			throw new ProjectionException( 'Both from and to team Id is invalid or empty.' );
		}
		if ($latestTransfers = $this->transferRepository->findActiveTransfer( $identifier[ 'player' ] )) {
			$this->inactiveLastActiveTransferByPlayerId($latestTransfers[0], $metadata);
		}
		$transferModel = $this->createTransferModel($identifier, $metadata);
		$transferModel->prePersist();
		$this->persistTransfer($transferModel);
		event(new PlayerWasTransferredProjectorEvent($transferModel));
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
				new DateTimeImmutable( $metadata[ 'startDate' ] ) )
			->setUpdatedAt(new DateTimeImmutable());
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
			throw new ProjectionException('Failed to persist transfer.', $exception->getCode(), $exception);
		}
	}
}