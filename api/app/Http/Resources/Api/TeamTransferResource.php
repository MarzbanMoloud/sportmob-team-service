<?php


namespace App\Http\Resources\Api;


use App\Models\ReadModels\Transfer;
use App\Services\Cache\Interfaces\TransferCacheServiceInterface;
use App\Utilities\Utility;
use DateTimeInterface;
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
	 * @param \Illuminate\Http\Request $data
	 * @return array|array[]
	 */
	public function toArray($data): array
	{
		return [
			'links' => [],
			'data' => [
				'transfers' => $this->makeTransferData(),
				'seasons' => $this->resource['seasons']
			]
		];
	}

	/**
	 * @return array
	 */
	private function makeTransferData()
	{
		return array_map(function (Transfer $transfer) {
			return [
				'transferId' => Utility::jsonEncode([
					'playerId' => $transfer->getPlayerId(),
					'startDate' => $transfer->getStartDate()->format(DateTimeInterface::ATOM)
				]),
				'player' => [
					'id' => $transfer->getPlayerId(),
					'name' => $this->client->getByLang($transfer->getPlayerName(), $this->lang),
					'position' => $this->client->getByLang($transfer->getPlayerPosition(), $this->lang)
				],
				'team' => [
					'to' => [
						'id' => $transfer->getToTeamId(),
						'name' => $this->client->getByLang($transfer->getToTeamName(), $this->lang)
					],
					'from' => [
						'id' => $transfer->getFromTeamId(),
						'name' => $this->client->getByLang($transfer->getFromTeamName(), $this->lang)
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
		}, $this->resource['transfers']);
	}
}