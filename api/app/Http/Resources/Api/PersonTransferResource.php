<?php


namespace App\Http\Resources\Api;


use App\Models\ReadModels\Transfer;
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
		$categorizedTransfers = [];
		$transferResult = [];

		foreach ($this->resource as $item) {
			/**
			 * @var Transfer $item
			 */
			$date = ($item->getDateFrom() != Transfer::getDateTimeImmutable()) ? $item->getDateFrom()->getTimestamp() : $item->getDateTo()->getTimestamp();
			$categorizedTransfers[$date] = $item;
		}

		ksort($categorizedTransfers);

		foreach ($categorizedTransfers as $transfer) {
			/**
			 * @var Transfer $transfer
			 */
			$transferFormatted = [
				'id' => $transfer->getId(),
				'person' => [
					'id' => $transfer->getPersonId(),
					'name' => ($transfer->getPersonName()) ? $this->client->getByLang($transfer->getPersonName(), $this->lang) : null,
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
				'startDate' => ($transfer->getDateFrom() != Transfer::getDateTimeImmutable()) ? $transfer->getDateFrom()->getTimestamp() : null,
				'endDate' => $transfer->getDateTo() ? $transfer->getDateTo()->getTimestamp() : null,
				'announcedDate' => $transfer->getAnnouncedDate() ? $transfer->getAnnouncedDate()->getTimestamp() : null,
				'contractDate' => $transfer->getContractDate() ? $transfer->getContractDate()->getTimestamp() : null,
				'type' => ($transfer->getType()) ? $this->client->getByLang($transfer->getType(), $this->lang) : null,
				'like' => $transfer->getLike(),
				'dislike' => $transfer->getDislike(),
				'season' => $transfer->getSeason()
			];
			$transferResult[] = $transferFormatted;
			if ($transfer->getType() == Transfer::TRANSFER_TYPE_LOAN)  {

				$teamTo = $transferFormatted['team']['to'];
				$teamFrom = $transferFormatted['team']['from'];

				$transferFormatted['id'] = str_replace('loan', 'loan_back', $transfer->getId());
				$transferFormatted['type'] = $this->client->getByLang('loan_back', $this->lang);
				$transferFormatted['team']['to'] = $teamFrom;
				$transferFormatted['team']['from'] = $teamTo;

				$transferResult[] = $transferFormatted;
			}
		}

		return [
			'links' => [],
			'data' => $transferResult
		];
	}
}