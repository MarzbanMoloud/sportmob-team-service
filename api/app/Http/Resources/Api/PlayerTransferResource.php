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
	 * PlayerTransferResource constructor.
	 * @param $resource
	 */
	public function __construct($resource)
	{
		$this->client = app(Client::class);
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
						'name' => $this->client->translate($transfer->getPlayerName(), $this->lang),
						'position' => $this->client->translate($transfer->getPlayerPosition(), $this->lang),
					],
					'team' => [
						'to' => [
							'id' => $transfer->getToTeamId(),
							'name' => $this->client->translate($transfer->getToTeamName(), $this->lang)
						],
						'from' => [
							'id' => $transfer->getFromTeamId(),
							'name' => $this->client->translate($transfer->getFromTeamName(), $this->lang),
						]
					],
					'marketValue' => $transfer->getMarketValue(),
					'startDate' => (string)$transfer->getStartDate()->getTimestamp(),
					'endDate' => (string)$transfer->getEndDate()->getTimestamp(),
					'announcedDate' => (string)$transfer->getAnnouncedDate()->getTimestamp(),
					'contractDate' => (string)$transfer->getContractDate()->getTimestamp(),
					'type' => $this->client->translate($transfer->getType(), $this->lang),
					'like' => $transfer->getLike(),
					'dislike' => $transfer->getDislike(),
					'season' => $transfer->getSeason()
				];
			}, $this->resource)
		];
	}
}