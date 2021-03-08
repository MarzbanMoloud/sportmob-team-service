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
					'id' => base64_encode(sprintf('%s#%s', $transfer->getPlayerId(), $transfer->getStartDate()->format(DateTimeInterface::ATOM))),
					'player' => [
						'id' => $transfer->getPlayerId(),
						'name' => $this->client->getByLang($transfer->getPlayerName(), $this->lang),
						'position' => $this->client->getByLang($transfer->getPlayerPosition(), $this->lang),
					],
					'team' => [
						'to' => [
							'id' => $transfer->getToTeamId(),
							'name' => $this->client->getByLang($transfer->getToTeamName(), $this->lang)
						],
						'from' => [
							'id' => $transfer->getFromTeamId(),
							'name' => $this->client->getByLang($transfer->getFromTeamName(), $this->lang),
						]
					],
					'marketValue' => $transfer->getMarketValue(),
					'startDate' => $transfer->getStartDate()->getTimestamp(),
					'endDate' => $transfer->getEndDate() ? $transfer->getEndDate()->getTimestamp() : '',
					'announcedDate' => $transfer->getAnnouncedDate() ? $transfer->getAnnouncedDate()->getTimestamp() : '',
					'contractDate' => $transfer->getContractDate() ? $transfer->getContractDate()->getTimestamp() : '',
					'type' => $this->client->getByLang($transfer->getType(), $this->lang),
					'like' => $transfer->getLike(),
					'dislike' => $transfer->getDislike(),
					'season' => $transfer->getSeason()
				];
			}, $this->resource)
		];
	}
}