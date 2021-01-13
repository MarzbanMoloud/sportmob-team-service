<?php


namespace App\Http\Resources\Api;


use App\Models\ReadModels\Transfer;
use App\Utilities\Utility;
use DateTimeInterface;
use Illuminate\Http\Resources\Json\JsonResource;
use SportMob\Translation\Client;


/**
 * Class PlayerTransferResource
 * @package App\Http\Resources\Api
 */
class PlayerTransferResource extends JsonResource
{
	private Client $client;
	private string $lang;

	/**
	 * TeamTransferResource constructor.
	 * @param $resource
	 */
	public function __construct($resource)
	{
		$this->client = app('TranslationClient');
		$this->lang = app()->getLocale();
		parent::__construct($resource);
	}

	/**
	 * @param \Illuminate\Http\Request $transfers
	 * @return array
	 */
	public function toArray($transfers): array
	{
		return [
			'links' => [],
			'data' => array_map(function (Transfer $transfer) {
				return [
					'transferId' => Utility::jsonEncode([
						'playerId' => $transfer->getPlayerId(),
						'startDate' => $transfer->getStartDate()->format(DateTimeInterface::ATOM)
					]),
					'player' => [
						'id' => $transfer->getPlayerId(),
						'name' => $transfer->getPlayerName(),
						'position' => $transfer->getPlayerPosition(),
					],
					'team' => [
						'to' => [
							'id' => $transfer->getToTeamId(),
							'name' =>$transfer->getToTeamName()
						],
						'from' => [
							'id' => $transfer->getFromTeamId(),
							'name' => $transfer->getFromTeamName(),
						]
					],
					'marketValue' => $transfer->getMarketValue(),
					'startDate' => (string)$transfer->getStartDate()->getTimestamp(),
					'endDate' => (string)$transfer->getEndDate()->getTimestamp(),
					'announcedDate' => (string)$transfer->getAnnouncedDate()->getTimestamp(),
					'contractDate' => (string)$transfer->getContractDate()->getTimestamp(),
					'type' => $transfer->getType(),
					'like' => $transfer->getLike(),
					'dislike' => $transfer->getDislike(),
					'season' => $transfer->getSeason()
				];
			}, $this->resource)
		];
	}
}