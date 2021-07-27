<?php


namespace App\Http\Resources\Api;


use App\ValueObjects\DTO\TransferDTO;
use Illuminate\Http\Resources\Json\JsonResource;
use SportMob\Translation\Client;


/**
 * Class PersonTransferResource
 * @package App\Http\Resources\Api
 */
class PersonTransferResource extends JsonResource
{
	private Client $client;
	private string $lang;

	/**
	 * PersonTransferResource constructor.
	 * @param $resource
	 */
	public function __construct($resource)
	{
		$this->client = app(Client::class);
		$this->lang = app()->getLocale();
		parent::__construct($resource);
	}

	/**
	 * @param $resource
	 * @return array
	 */
	public function toArray($resource): array
	{
		return [
			'links' => [],
			'data' => array_map(function (TransferDTO $transfer) {
				$result = [
					'id' => $transfer->getId(),
					'player' => [
						'id' => $transfer->getPersonId(),
						'name' => ($transfer->getPersonName()) ? $this->client->getByLang($transfer->getPersonName(), $this->lang) : null,
					],
					'team' => [
						'to' => [
							'id' => $transfer->getTeamToId(),
							'name' => ($transfer->getTeamToName()) ? $this->client->getByLang($transfer->getTeamToName(), $this->lang) : null
						],
						'from' => [
							'id' => $transfer->getTeamFromId(),
							'name' => ($transfer->getTeamFromName()) ? $this->client->getByLang($transfer->getTeamFromName(), $this->lang) : null,
						]
					],
					'marketValue' => $transfer->getMarketValue(),
					'startDate' => $transfer->getStartDate(),
					'endDate' => $transfer->getEndDate(),
					'announcedDate' => $transfer->getAnnouncedDate(),
					'contractDate' => $transfer->getContractDate(),
					'type' => ($transfer->getType()) ? $this->client->getByLang($transfer->getType(), $this->lang) : null,
					'like' => $transfer->getLike(),
					'dislike' => $transfer->getDislike(),
					'season' => $transfer->getSeason()
				];
				if (is_null($transfer->getTeamFromId()) && is_null($transfer->getTeamFromName())) {
					unset($result['team']['from']);
				}
				return $result;
			}, $this->resource)
		];
	}
}