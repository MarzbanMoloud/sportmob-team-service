<?php


namespace App\Traits;


use App\Models\ReadModels\Transfer;
use App\ValueObjects\Broker\Mediator\Message;
use DateTimeImmutable;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * Trait TransferLogicTrait
 * @package App\Traits
 */
trait TransferLogicTrait
{
	/**
	 * @param Message $message
	 * @param array $memberships
	 * @param string $personId
	 * @param string $personType
	 * @throws Exception
	 */
	public function transformByPerson(Message $message, array $memberships, string $personId, string $personType)
	{
		$flag = false;

		foreach ($memberships as $key => $membership) {

			$teamItem = $this->findTeam($membership['teamId']);
			$teamName1 = ($teamItem) ? $teamItem->getName()->getOriginal() : null;

			if (is_null($membership['onLoanFrom']) && $flag == false) {
				$flag = true;

				$transfer = (new Transfer())
					->setId($membership['id'])
					->setPersonId($personId)
					->setPersonType($personType)
					->setToTeamId($membership['teamId'])
					->setToTeamName($teamName1)
					->setDateFrom($membership['dateFrom'] ? new DateTimeImmutable($membership['dateFrom']) : Transfer::getDateTimeImmutable())
					->setDateTo($membership['dateTo'] ? new DateTimeImmutable($membership['dateTo']) : null)
					->setType(Transfer::TRANSFER_TYPE_TRANSFERRED);

				$transfer->prePersist();
				$this->persistTransfer($transfer, $message);

				continue;
			}

			if (! is_null($membership['onLoanFrom'])) {
				$teamItem = $this->findTeam($membership['onLoanFrom']);
				$teamName2 = ($teamItem) ? $teamItem->getName()->getOriginal() : null;

				$transfer = (new Transfer())
					->setId(sprintf('%s_%s', $membership['id'], Transfer::TRANSFER_TYPE_LOAN))
					->setPersonId($personId)
					->setPersonType($personType)
					->setFromTeamId($membership['onLoanFrom'])
					->setFromTeamName($teamName2)
					->setToTeamId($membership['teamId'])
					->setToTeamName($teamName1)
					->setDateFrom($membership['dateFrom'] ? new DateTimeImmutable($membership['dateFrom']) : Transfer::getDateTimeImmutable())
					->setDateTo($membership['dateTo'] ? new DateTimeImmutable($membership['dateTo']) : null)
					->setType(Transfer::TRANSFER_TYPE_LOAN);

				$transfer->prePersist();
				$this->persistTransfer($transfer, $message);

				$transfer = (new Transfer())
					->setId(sprintf('%s_%s', $membership['id'], Transfer::TRANSFER_TYPE_LOAN_BACK))
					->setPersonId($personId)
					->setPersonType($personType)
					->setToTeamId($membership['onLoanFrom'])
					->setToTeamName($teamName2)
					->setFromTeamId($membership['teamId'])
					->setFromTeamName($teamName1)
					->setDateFrom($membership['dateFrom'] ? new DateTimeImmutable($membership['dateFrom']) : Transfer::getDateTimeImmutable())
					->setDateTo($membership['dateTo'] ? new DateTimeImmutable($membership['dateTo']) : null)
					->setType(Transfer::TRANSFER_TYPE_LOAN_BACK);

				$transfer->prePersist();
				$this->persistTransfer($transfer, $message);
			}

			if (is_null($membership['onLoanFrom']) && $flag == true) {
				if (!is_null($memberships[$key-1]['onLoanFrom'])) {
					$teamItem = $this->findTeam($memberships[$key-1]['onLoanFrom']);
					$teamName3 = ($teamItem) ? $teamItem->getName()->getOriginal() : null;

					$transfer = (new Transfer())
						->setId($membership['id'])
						->setPersonId($personId)
						->setPersonType($personType)
						->setToTeamId($membership['teamId'])
						->setToTeamName($teamName1)
						->setFromTeamId($memberships[$key-1]['onLoanFrom'])
						->setFromTeamName($teamName3)
						->setDateFrom($membership['dateFrom'] ? new DateTimeImmutable($membership['dateFrom']) : Transfer::getDateTimeImmutable())
						->setDateTo($membership['dateTo'] ? new DateTimeImmutable($membership['dateTo']) : null)
						->setType(Transfer::TRANSFER_TYPE_TRANSFERRED);

					$transfer->prePersist();
					$this->persistTransfer($transfer, $message);
				} else {
					$teamItem = $this->findTeam($memberships[$key-1]['teamId']);
					$teamName4 = ($teamItem) ? $teamItem->getName()->getOriginal() : null;

					$transfer = (new Transfer())
						->setId($membership['id'])
						->setPersonId($personId)
						->setPersonType($personType)
						->setToTeamId($membership['teamId'])
						->setToTeamName($teamName1)
						->setFromTeamId($memberships[$key-1]['teamId'])
						->setFromTeamName($teamName4)
						->setDateFrom($membership['dateFrom'] ? new DateTimeImmutable($membership['dateFrom']) : Transfer::getDateTimeImmutable())
						->setDateTo($membership['dateTo'] ? new DateTimeImmutable($membership['dateTo']) : null)
						->setType(Transfer::TRANSFER_TYPE_TRANSFERRED);

					$transfer->prePersist();
					$this->persistTransfer($transfer, $message);
				}
			}
		}
	}

	/**
	 * @param string $teamId
	 * @param string|null $season
	 * @return array
	 */
	private function transformByTeam(string $teamId, ?string $season = null)
	{
		$transfers = array_merge(
			$this->transferRepository->findAllByTeamIdAndSeason(Transfer::ATTR_TO_TEAM_ID, $teamId) ?? [],
			$this->transferRepository->findAllByTeamIdAndSeason(Transfer::ATTR_FROM_TEAM_ID, $teamId) ?? []
		);

		$transformItems = [];

		foreach ($transfers as $transfer) {
			/** @var Transfer $transfer */
			if (is_null($transfer->getSeason())) {
				continue;
			}
			$transformItems[$transfer->getSeason()][] = $transfer;
		}

		$seasons = array_keys($transformItems);
		//TODO:: merge conditions.
		if (! $seasons) {
			throw new NotFoundHttpException();
		}

		if ((!is_null($season) && !in_array($season, $seasons))) {
			throw new NotFoundHttpException();
		}

		self::sortBySeason($seasons);

		foreach ($transformItems as $seasonKey => $transfer) {
			$this->transferCacheService->putTransfersByTeam($teamId, $seasonKey, [
				'transfers' => $transfer,
				'seasons' => $seasons
			]);
		}

		if (is_null($season)) {
			$season = $seasons[0];
		}

		return [
			'transfers' => $transformItems[$season],
			'seasons' => $seasons
		];
	}

	/**
	 * @param array $seasons
	 */
	private static function sortBySeason(array &$seasons)
	{
		usort($seasons,
			static function ($first, $second) {
				if ($first === $second) {
					return 0;
				}
				return ($first < $second) ? 1 : -1;
			});
	}
}