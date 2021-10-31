<?php


namespace App\Http\Resources\Api;


use App\Models\ReadModels\Transfer;
use App\Services\Cache\Interfaces\TransferCacheServiceInterface;
use App\ValueObjects\Response\NameResponse;
use App\ValueObjects\Response\PersonResponse;
use App\ValueObjects\Response\TeamResponse;
use App\ValueObjects\Response\TransferResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use SportMob\Translation\Client;


/**
 * Class TeamTransferResource
 * @package App\Http\Resources\Api
 */
class TeamTransferResource extends JsonResource
{
	private Client $client;
	private string $lang;
	private TransferCacheServiceInterface $transferCacheService;

	/**
	 * TeamTransferResource constructor.
	 * @param $resource
	 */
	public function __construct($resource)
	{
		$this->client = app(Client::class);
		$this->lang = app()->getLocale();
		$this->transferCacheService = app(TransferCacheServiceInterface::class);
		parent::__construct($resource);
	}

	/**
	 * @param $resource
	 * @return array|array[]
	 */
	public function toArray($resource): array
	{
		return [
			'links' => (object) null,
			'data' => $this->makeTransferData()
		];
	}

	private function makeTransferData(): array
	{
		return array_map(function (Transfer $transfer) {
			return TransferResponse::create(
				$transfer->getId(),
				TeamResponse::create(
					$transfer->getToTeamId(),
					NameResponse::create($this->client->getByLang($transfer->getToTeamName(), $this->lang))
				),
				$transfer->getType(),
				$transfer->getLike(),
				$transfer->getDislike(),
				$transfer->getPersonId() ? PersonResponse::create(
					$transfer->getPersonId(),
					$transfer->getPersonName() ? NameResponse::create($this->client->getByLang($transfer->getPersonName(), $this->lang)) : null
				) : null,
				$transfer->getFromTeamId() ? TeamResponse::create(
					$transfer->getFromTeamId(),
					NameResponse::create(
						$transfer->getFromTeamId(),
						$this->client->getByLang($transfer->getFromTeamName(), $this->lang)
					)
				) : null,
				$transfer->getSeason(),
				($transfer->getDateFrom() != Transfer::getDateTimeImmutable()) ? $transfer->getDateFrom()->getTimestamp() : null,
				$transfer->getDateTo() ? $transfer->getDateTo()->getTimestamp() : null,
				$transfer->getMarketValue()
			)->toArray();

		}, $this->resource['transfers']);
	}
}