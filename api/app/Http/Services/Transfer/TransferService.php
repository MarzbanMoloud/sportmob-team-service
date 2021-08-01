<?php


namespace App\Http\Services\Transfer;


use App\Exceptions\DynamoDB\DynamoDBException;
use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Exceptions\Projection\ProjectionException;
use App\Exceptions\UserActionTransferNotAllow;
use App\Services\Cache\TransferCacheService;
use App\Traits\TransferLogicTrait;
use App\ValueObjects\DTO\PersonTransferDTO;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ReadModels\Transfer;
use App\Models\Repositories\TransferRepository;
use App\Services\Cache\Interfaces\TransferCacheServiceInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * Class TransferService
 * @package App\Http\Services\Transfer
 */
class TransferService
{
	use TransferLogicTrait;

	const TRANSFER_LIKE = 'like';
	const TRANSFER_DISLIKE = 'dislike';

	private TransferRepository $transferRepository;
	private TransferCacheServiceInterface $transferCacheService;
	private array $seasons = [];

	/**
	 * TransferService constructor.
	 * @param TransferRepository $transferRepository
	 * @param TransferCacheServiceInterface $transferCacheService
	 */
	public function __construct(
		TransferRepository $transferRepository,
		TransferCacheServiceInterface $transferCacheService
	) {
		$this->transferRepository = $transferRepository;
		$this->transferCacheService = $transferCacheService;
	}

	/**
	 * @param string $teamId
	 * @param string|null $season
	 * @return mixed
	 */
	public function listByTeam(string $teamId, ?string $season = null)
	{
		return $this->transferCacheService->rememberForeverTransfersByTeam(function () use ($teamId, $season) {
			return $this->transformByTeam($teamId, $season);
		}, $teamId, $season);
	}

	/**
	 * @param string $id
	 * @return array
	 */
	public function listByPerson(string $id): array
	{
		$transfers = $this->transferCacheService->rememberForeverTransfersByPerson($id, function () use ($id) {
			return $this->transferRepository->findByPersonId($id);
		});

		if (!$transfers) {
			throw new NotFoundHttpException();
		}

		array_reverse($transfers);

		return $transfers;
	}

	/**
	 * @param string $action
	 * @param string $user
	 * @param string $transfer
	 * @throws ProjectionException
	 * @throws UserActionTransferNotAllow
	 */
	public function userActionTransfer(string $action, string $user, string $transfer)
	{
		if ($this->transferCacheService->hasUserActionTransfer($action, $user, $transfer)) {
			throw new UserActionTransferNotAllow();
		}

		$transferItem = $this->findTransfer($transfer);

		if ($action == self::TRANSFER_LIKE) {

			if ($this->transferCacheService->hasUserActionTransfer(self::TRANSFER_DISLIKE, $user, $transfer)) {
				$transferItem->setDislike($transferItem->getDislike() - 1);
				$this->transferCacheService->forget(TransferCacheService::getUserActionTransferKey(self::TRANSFER_DISLIKE, $user, $transfer));
				$transferItem->setLike($transferItem->getLike() + 1);
			} else {
				$transferItem->setLike($transferItem->getLike() + 1);
			}

		} else if ($action == self::TRANSFER_DISLIKE) {

			if ($this->transferCacheService->hasUserActionTransfer(self::TRANSFER_LIKE, $user, $transfer)) {
				$transferItem->setLike($transferItem->getLike() - 1);
				$this->transferCacheService->forget(TransferCacheService::getUserActionTransferKey(self::TRANSFER_LIKE, $user, $transfer));
				$transferItem->setDislike($transferItem->getDislike() + 1);
			} else {
				$transferItem->setDislike($transferItem->getDislike() + 1);
			}

		}

		try {
			$this->transferRepository->persist($transferItem);
		} catch (DynamoDBRepositoryException $exception) {
			throw new ProjectionException('Failed to update transfer.', $exception->getCode(), $exception);
		}

		$this->transferCacheService->putUserActionTransfer($action, $user, $transfer);
	}

	/**
	 * @param PersonTransferDTO $personTransferDTO
	 * @throws DynamoDBException
	 */
	public function updateItem(PersonTransferDTO $personTransferDTO)
	{
		$transferItem = $this->findTransfer($personTransferDTO->getTransferId());
		try {
			$transferItem
				->setContractDate((new \DateTimeImmutable())->setTimestamp($personTransferDTO->getContractDate()))
				->setAnnouncedDate((new \DateTimeImmutable())->setTimestamp($personTransferDTO->getAnnouncedDate()))
				->setMarketValue($personTransferDTO->getMarketValue());
			$this->transferRepository->persist($transferItem);
		} catch (\Exception $exception) {
			throw new DynamoDBException(
				'Transfer Update failed.',
				Response::HTTP_UNPROCESSABLE_ENTITY,
				$exception,
				config('common.error_codes.transfer_update_failed')
			);
		}
		$this->transferCacheService->forget(TransferCacheService::getTransferByPersonKey($transferItem->getPersonId()));
		$this->transferCacheService->forget(TransferCacheService::getTransferByTeamKey($transferItem->getToTeamId(), $transferItem->getSeason()));
		$this->transferCacheService->forget(TransferCacheService::getTransferByTeamKey($transferItem->getFromTeamId(), $transferItem->getSeason()));
	}


	/**
	 * @param string $transfer
	 * @return Transfer
	 */
	private function findTransfer(string $transfer): Transfer
	{
		/*** @var Transfer $transferItem */
		$transferItem = $this->transferRepository->find([
			'id' => $transfer,
		]);
		if (!$transferItem) {
			throw new NotFoundHttpException();
		}
		return $transferItem;
	}
}