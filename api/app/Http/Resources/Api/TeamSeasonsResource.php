<?php


namespace App\Http\Resources\Api;


use App\Models\ReadModels\Transfer;
use App\Services\Cache\Interfaces\TransferCacheServiceInterface;
use App\ValueObjects\Response\Name;
use App\ValueObjects\Response\NameResponse;
use App\ValueObjects\Response\Person;
use App\ValueObjects\Response\PersonResponse;
use App\ValueObjects\Response\Team;
use App\ValueObjects\Response\TeamResponse;
use App\ValueObjects\Response\TransferResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use SportMob\Translation\Client;


/**
 * Class TeamSeasonsResource
 * @package App\Http\Resources\Api
 */
class TeamSeasonsResource extends JsonResource
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
			'data' => $this->makeSeasonsData()
		];
	}

	/**
	 * @return array
	 */
	private function makeSeasonsData()
	{
		$seasons = [];
		foreach ($this->resource['seasons'] as $season) {
			if ($season == Transfer::DEFAULT_VALUE){
				continue;
			}
			$seasons[] = $season;
		}
		return $seasons;
	}
}