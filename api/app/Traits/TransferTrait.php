<?php


namespace App\Traits;


use App\Models\ReadModels\Transfer;
use App\ValueObjects\DTO\TransferDTO;


/**
 * Trait TransferTrait
 * @package App\Traits
 */
trait TransferTrait
{
	/**
	 * @param array $transfers
	 * @return array
	 */
	public function transform(array $transfers): array
	{
		$flag = false;
		$result = [];

		foreach ($transfers as $key => $transfer) {
			/** @var Transfer $transfer */

			if ($transfer->getDateFrom() == Transfer::getDateTimeImmutable() && is_null($transfer->getDateTo())) {
				continue;
			}

			if ($transfer->getOnLoanFromId() == Transfer::DEFAULT_VALUE && $flag == false) {
				$flag = true;
				$result[] = (new TransferDTO())
					->setId($transfer->getId())
					->setPersonId($transfer->getPersonId())
					->setPersonName($transfer->getPersonName())
					->setTeamToId($transfer->getTeamId())
					->setTeamToName($transfer->getTeamName())
					->setMarketValue($transfer->getMarketValue())
					->setStartDate($transfer->getDateFrom()->getTimestamp())
					->setEndDate($transfer->getDateTo() ? $transfer->getDateTo()->getTimestamp() : null)
					->setAnnouncedDate($transfer->getAnnouncedDate() ? $transfer->getAnnouncedDate()->getTimestamp() : null)
					->setContractDate($transfer->getAnnouncedDate() ? $transfer->getAnnouncedDate()->getTimestamp() : null)
					->setLike($transfer->getLike())
					->setDislike($transfer->getDislike())
					->setSeason(($transfer->getSeason() != Transfer::DEFAULT_VALUE) ? $transfer->getSeason() : null)
					->setType(Transfer::TRANSFER_TYPE_TRANSFERRED);
				continue;
			}

			if ($transfer->getOnLoanFromId() != Transfer::DEFAULT_VALUE) {
				$result[] = (new TransferDTO())
					->setId(sprintf('%s#%s', $transfer->getId(), Transfer::TRANSFER_TYPE_LOAN))
					->setPersonId($transfer->getPersonId())
					->setPersonName($transfer->getPersonName())
					->setTeamToId($transfer->getTeamId())
					->setTeamToName($transfer->getTeamName())
					->setTeamFromId($transfer->getOnLoanFromId())
					->setTeamFromName($transfer->getOnLoanFromName())
					->setMarketValue($transfer->getMarketValue())
					->setStartDate($transfer->getDateFrom()->getTimestamp())
					->setEndDate($transfer->getDateTo() ? $transfer->getDateTo()->getTimestamp() : null)
					->setAnnouncedDate($transfer->getAnnouncedDate() ? $transfer->getAnnouncedDate()->getTimestamp() : null)
					->setContractDate($transfer->getAnnouncedDate() ? $transfer->getAnnouncedDate()->getTimestamp() : null)
					->setLike($transfer->getLike())
					->setDislike($transfer->getDislike())
					->setSeason(($transfer->getSeason() != Transfer::DEFAULT_VALUE) ? $transfer->getSeason() : null)
					->setType(Transfer::TRANSFER_TYPE_LOAN);

				$result[] = (new TransferDTO())
					->setId(sprintf('%s#%s', $transfer->getId(), Transfer::TRANSFER_TYPE_LOAN_BACK))
					->setPersonId($transfer->getPersonId())
					->setPersonName($transfer->getPersonName())
					->setTeamToId($transfer->getOnLoanFromId())
					->setTeamToName($transfer->getOnLoanFromName())
					->setTeamFromId($transfer->getTeamId())
					->setTeamFromName($transfer->getTeamName())
					->setMarketValue($transfer->getMarketValue())
					->setStartDate($transfer->getDateFrom()->getTimestamp())
					->setEndDate($transfer->getDateTo() ? $transfer->getDateTo()->getTimestamp() : null)
					->setAnnouncedDate($transfer->getAnnouncedDate() ? $transfer->getAnnouncedDate()->getTimestamp() : null)
					->setContractDate($transfer->getAnnouncedDate() ? $transfer->getAnnouncedDate()->getTimestamp() : null)
					->setLike($transfer->getLike())
					->setDislike($transfer->getDislike())
					->setSeason(($transfer->getSeason() != Transfer::DEFAULT_VALUE) ? $transfer->getSeason() : null)
					->setType(Transfer::TRANSFER_TYPE_LOAN_BACK);
			}

			if ($transfer->getOnLoanFromId() == Transfer::DEFAULT_VALUE && $flag == true) {
				if ($transfers[$key-1]->getOnLoanFromId() != Transfer::DEFAULT_VALUE) {
					$result[] = (new TransferDTO())
						->setId($transfer->getId())
						->setPersonId($transfer->getPersonId())
						->setPersonName($transfer->getPersonName())
						->setTeamToId($transfer->getTeamId())
						->setTeamToName($transfer->getTeamName())
						->setTeamFromId($transfers[$key-1]->getOnLoanFromId())
						->setTeamFromName($transfers[$key-1]->getOnLoanFromName())
						->setMarketValue($transfer->getMarketValue())
						->setStartDate($transfer->getDateFrom()->getTimestamp())
						->setEndDate($transfer->getDateTo() ? $transfer->getDateTo()->getTimestamp() : null)
						->setAnnouncedDate($transfer->getAnnouncedDate() ? $transfer->getAnnouncedDate()->getTimestamp() : null)
						->setContractDate($transfer->getAnnouncedDate() ? $transfer->getAnnouncedDate()->getTimestamp() : null)
						->setLike($transfer->getLike())
						->setDislike($transfer->getDislike())
						->setSeason(($transfer->getSeason() != Transfer::DEFAULT_VALUE) ? $transfer->getSeason() : null)
						->setType(Transfer::TRANSFER_TYPE_TRANSFERRED);
				} else {
					$result[] = (new TransferDTO())
						->setId($transfer->getId())
						->setPersonId($transfer->getPersonId())
						->setPersonName($transfer->getPersonName())
						->setTeamToId($transfer->getTeamId())
						->setTeamToName($transfer->getTeamName())
						->setTeamFromId($transfers[$key-1]->getTeamId())
						->setTeamFromName($transfers[$key-1]->getTeamName())
						->setMarketValue($transfer->getMarketValue())
						->setStartDate($transfer->getDateFrom()->getTimestamp())
						->setEndDate($transfer->getDateTo() ? $transfer->getDateTo()->getTimestamp() : null)
						->setAnnouncedDate($transfer->getAnnouncedDate() ? $transfer->getAnnouncedDate()->getTimestamp() : null)
						->setContractDate($transfer->getAnnouncedDate() ? $transfer->getAnnouncedDate()->getTimestamp() : null)
						->setLike($transfer->getLike())
						->setDislike($transfer->getDislike())
						->setSeason(($transfer->getSeason() != Transfer::DEFAULT_VALUE) ? $transfer->getSeason() : null)
						->setType(Transfer::TRANSFER_TYPE_TRANSFERRED);
				}
			}
		}
		return array_reverse($result);
	}
}