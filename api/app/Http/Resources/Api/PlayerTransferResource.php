<?php


namespace App\Http\Resources\Api;


use App\Models\ReadModels\Transfer;
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
					'id' => base64_encode(sprintf('%s#%s', $transfer->getPlayerId(), $transfer->getStartDate()->format(DateTimeInterface::ATOM))),
					'player' => [
						'id' => $transfer->getPlayerId(),
						'name' => ($transfer->getPlayerName()) ? $this->client->getByLang($transfer->getPlayerName(), $this->lang) : null,
						'position' => ($transfer->getPlayerPosition()) ? $this->client->getByLang($transfer->getPlayerPosition(), $this->lang) : null,
					],
					'team' => [
						'to' => [
							'id' => $transfer->getToTeamId(),
							'name' => ($transfer->getToTeamName()) ? $this->client->getByLang($transfer->getToTeamName(), $this->lang) : null
						],
						'from' => [
							'id' => $transfer->getFromTeamId(),
							'name' => ($transfer->getFromTeamName()) ? $this->client->getByLang($transfer->getFromTeamName(), $this->lang) : null,
						]
					],
					'marketValue' => $transfer->getMarketValue(),
					'startDate' => $transfer->getStartDate()->getTimestamp(),
					'endDate' => $transfer->getEndDate() ? $transfer->getEndDate()->getTimestamp() : null,
					'announcedDate' => $transfer->getAnnouncedDate() ? $transfer->getAnnouncedDate()->getTimestamp() : null,
					'contractDate' => $transfer->getContractDate() ? $transfer->getContractDate()->getTimestamp() : null,
					'type' => ($transfer->getType()) ? $this->client->getByLang($transfer->getType(), $this->lang) : null,
					'like' => $transfer->getLike(),
					'dislike' => $transfer->getDislike(),
					'season' => $transfer->getSeason()
				];
			}, $this->resource)
		];
	}
}