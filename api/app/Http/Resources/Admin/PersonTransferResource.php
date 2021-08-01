<?php


namespace App\Http\Resources\Admin;


use App\Models\ReadModels\Transfer;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * Class PersonTransferResource
 * @package App\Http\Resources\Admin
 */
class PersonTransferResource extends JsonResource
{
	/**
	 * @param $resource
	 * @return array|array[]
	 */
	public function toArray($resource): array
	{
		return [
			'links' => [],
			'data' => array_map(function (Transfer $transfer) {
				return [
					'id' => $transfer->getId(),
					'person' => [
						'id' => $transfer->getPersonId(),
						'name' => $transfer->getPersonName(),
					],
					'team' => [
						'to' => [
							'id' => $transfer->getToTeamId(),
							'name' => $transfer->getToTeamName()
						],
						'from' => [
							'id' => $transfer->getFromTeamId(),
							'name' => $transfer->getFromTeamName(),
						]
					],
					'startDate' => ($transfer->getDateFrom() != Transfer::getDateTimeImmutable()) ? $transfer->getDateFrom()->getTimestamp() : null,
					'endDate' => $transfer->getDateTo() ? $transfer->getDateTo()->getTimestamp() : null,
					'marketValue' => $transfer->getMarketValue(),
					'announcedDate' => $transfer->getAnnouncedDate() ? $transfer->getAnnouncedDate()->getTimestamp() : null,
					'contractDate' => $transfer->getContractDate()? $transfer->getContractDate()->getTimestamp() : null,
					'type' => $transfer->getType(),
					'season' => $transfer->getSeason()
				];
			}, $this->resource)
		];
	}
}