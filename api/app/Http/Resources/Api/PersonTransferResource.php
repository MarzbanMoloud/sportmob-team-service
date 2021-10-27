<?php


namespace App\Http\Resources\Api;


use App\Models\ReadModels\Transfer;
use App\ValueObjects\Response\NameResponse;
use App\ValueObjects\Response\PersonResponse;
use App\ValueObjects\Response\TeamResponse;
use App\ValueObjects\Response\TransferResponse;
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
			$transferFormatted = TransferResponse::create(
				$transfer->getId(),
				TeamResponse::create(
					$transfer->getToTeamId(),
					$transfer->getToTeamName() ? NameResponse::create($this->client->getByLang($transfer->getToTeamName(), $this->lang)) : null
				),
				$this->client->getByLang($transfer->getType(), $this->lang),
				$transfer->getLike(),
				$transfer->getDislike(),
				PersonResponse::create(
					$transfer->getPersonId(),
					($transfer->getPersonName()) ? NameResponse::create($this->client->getByLang($transfer->getPersonName(), $this->lang)) : null
				),
				$transfer->getFromTeamId() ? TeamResponse::create(
					$transfer->getFromTeamId(),
					($transfer->getFromTeamName()) ? NameResponse::create($this->client->getByLang($transfer->getFromTeamName(), $this->lang)) : null
				) : null,
				$transfer->getSeason(),
				($transfer->getDateFrom() != Transfer::getDateTimeImmutable()) ? $transfer->getDateFrom()->getTimestamp() : null,
				$transfer->getDateTo() ? $transfer->getDateTo()->getTimestamp() : null,
				$transfer->getMarketValue()
			)->toArray();

			$transferResult[] = $transferFormatted;
			if ($transfer->getType() == Transfer::TRANSFER_TYPE_LOAN)  {

				$teamTo = $transferFormatted['toTeam'];
				$teamFrom = $transferFormatted['fromTeam'];

				$transferFormatted['id'] = str_replace('loan', 'loan_back', $transfer->getId());
				$transferFormatted['type'] = $this->client->getByLang('loan_back', $this->lang);
				$transferFormatted['toTeam'] = $teamFrom;
				$transferFormatted['fromTeam'] = $teamTo;

				$transferResult[] = $transferFormatted;
			}
		}

		return [
			'links' => [],
			'data' => array_reverse($transferResult)
		];
	}
}