<?php


namespace App\Http\Resources\Admin;


use App\Models\ReadModels\Transfer;
use App\Utilities\Utility;
use DateTimeInterface;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * Class PlayerTransferResource
 * @package App\Http\Resources\Admin
 */
class PlayerTransferResource extends JsonResource
{
	/**
	 * @param \Illuminate\Http\Request $transfers
	 * @return array|array[]
	 */
	public function toArray($transfers): array
	{
		return [
			'links' => [],
			'data' => array_map(function (Transfer $transfer) {
				return [
					'id' => base64_encode(sprintf('%s#%s', $transfer->getPlayerId(), $transfer->getStartDate()->format(DateTimeInterface::ATOM))),
					'player' => [
						'id' => $transfer->getPlayerId(),
						'name' => $transfer->getPlayerName(),
						'position' => $transfer->getPlayerPosition(),
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
					'startDate' => $transfer->getStartDate()->getTimestamp(),
					'endDate' => $transfer->getEndDate() ? $transfer->getEndDate()->getTimestamp() : '',
					'marketValue' => $transfer->getMarketValue(),
					'announcedDate' => $transfer->getAnnouncedDate()->getTimestamp(),
					'contractDate' => $transfer->getContractDate()->getTimestamp(),
					'type' => $transfer->getType(),
					'season' => $transfer->getSeason()
				];
			}, $this->resource)
		];
	}
}