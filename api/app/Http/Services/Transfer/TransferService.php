<?php


namespace App\Http\Services\Transfer;


use App\Exceptions\DynamoDB\DynamoDBException;
use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Exceptions\Projection\ProjectionException;
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\UserActionTransferNotAllow;
use App\Services\Cache\TransferCacheService;
use App\ValueObjects\DTO\PlayerTransferDTO;
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
		if (is_null($season)) {
			$season = $this->seasons[0];
		}
		return $this->transferCacheService->rememberForeverTransferByTeam($teamId, $season,
			function () use ($teamId, $season) {
				return $this->transferRepository->findByTeamId($teamId, $season);
		});
	}

	/**
	 * @param string $teamId
	 * @return mixed
	 */
	public function getAllSeasons(string $teamId)
	{
		$seasons = $this->transferCacheService->rememberForeverAllSeasonsByTeam($teamId, function () use ($teamId) {
			return $this->transferRepository->getAllSeasons($teamId);
		});
		if (! $seasons) {
			throw new NotFoundHttpException();
		}
		rsort($seasons);
		return $seasons;
	}

	/**
	 * @param string $playerId
	 * @return mixed
	 */
	public function listByPlayer(string $playerId)
	{
		$transfers = $this->transferCacheService->rememberForeverTransferByPlayer($playerId, function () use ($playerId) {
			return $this->transferRepository->findByPlayerId($playerId);
		});
		if (!$transfers) {
			throw new NotFoundHttpException();
		}
		self::sortBySeason($transfers);
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
	 * @param PlayerTransferDTO $playerTransferDTO
	 * @throws DynamoDBException
	 */
	public function updateItem(PlayerTransferDTO $playerTransferDTO)
	{
		$transferItem = $this->findTransfer($playerTransferDTO->getTransferId());
		try {
			$transferItem
				->setContractDate((new \DateTimeImmutable())->setTimestamp($playerTransferDTO->getContractDate()))
				->setAnnouncedDate((new \DateTimeImmutable())->setTimestamp($playerTransferDTO->getAnnouncedDate()))
				->setMarketValue($playerTransferDTO->getMarketValue());
			$this->transferRepository->persist($transferItem);
		} catch (\Exception $exception) {
			throw new DynamoDBException(
				'Transfer Update failed.',
				Response::HTTP_UNPROCESSABLE_ENTITY,
				$exception,
				config('common.error_codes.transfer_update_failed')
			);
		}
		$this->transferCacheService->forget(TransferCacheService::getTransferByPlayerKey($transferItem->getPlayerId()));
		$this->transferCacheService->forget(TransferCacheService::getTransferByTeamKey($transferItem->getToTeamId(), $transferItem->getSeason()));
		$this->transferCacheService->forget(TransferCacheService::getTransferByTeamKey($transferItem->getFromTeamId(), $transferItem->getSeason()));
	}

	/**
	 * @param array $transfers
	 */
	private static function sortBySeason(array &$transfers)
	{
		usort($transfers,
			static function (Transfer $first, Transfer $second) {
				if ($first->getSeason() === $second->getSeason()) {
					return 0;
				}
				return ($first->getSeason() > $second->getSeason()) ? 1 : -1;
			});
	}

	/**
	 * @param string $transfer
	 * @return Transfer
	 */
	private function findTransfer(string $transfer): Transfer
	{
		$transferDecoded = base64_decode($transfer);
		list($playerId, $startDate) = explode('#', $transferDecoded);
		/**
		 * @var Transfer $transferItem
		 */
		$transferItem = $this->transferRepository->find([
			'playerId' => $playerId,
			'startDate' => $startDate
		]);
		if (!$transferItem) {
			throw new NotFoundHttpException();
		}
		return $transferItem;
	}

	/**
	 * @param $seasons
	 * @return $this
	 */
	public function setSeasons($seasons): TransferService
	{
		$this->seasons = $seasons;
		return $this;
	}
}