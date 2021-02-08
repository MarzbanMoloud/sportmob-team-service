<?php


namespace App\Http\Resources\Api;


use App\Models\ReadModels\Trophy;
use Illuminate\Http\Resources\Json\JsonResource;
use SportMob\Translation\Client;


/**
 * Class TrophiesByCompetitionResource
 * @package App\Http\Resources\Api
 */
class TrophiesByCompetitionResource extends JsonResource
{
	private Client $client;
	private string $lang;

	/**
	 * TrophiesByTeamResource constructor.
	 * @param $resource
	 */
	public function __construct($resource)
	{
		$this->client = app(Client::class);
		$this->lang = app()->getLocale();
		parent::__construct($resource);
	}

	/**
	 * @param \Illuminate\Http\Request $data
	 * @return array
	 */
	public function toArray($data): array
	{
		return [
			'links' => [],
			'data' => $this->makeData()
		];
	}

	/**
	 * @return array
	 */
	private function makeData(): array
	{
		$categorizedData = [];
		foreach ($this->resource as $trophy) {
			/**
			 * @var Trophy $trophy
			 */
			$categorizedData[$trophy->getTournamentId()][] = $trophy;
		}
		$data = [];
		$i = 0;
		foreach ($categorizedData as $tournamentId => $tournament) {
			/**
			 * @var Trophy $trophy
			 */
			foreach ($tournament as $trophy) {
				$data[$i]['season'] = $trophy->getTournamentSeason();
				$data[$i]['id'] = $trophy->getTournamentId();
				$data[$i][$trophy->getPosition()] = [
					'id' => $trophy->getTeamId(),
					'name' => $trophy->getTeamName()
				];
			}
			$i++;
		}
		return $data;
	}
}