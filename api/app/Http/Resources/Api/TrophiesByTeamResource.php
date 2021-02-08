<?php


namespace App\Http\Resources\Api;


use Illuminate\Http\Resources\Json\JsonResource;
use SportMob\Translation\Client;


/**
 * Class TrophiesByTeamResource
 * @package App\Http\Resources\Api
 */
class TrophiesByTeamResource extends JsonResource
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
		foreach ($this->resource as $item) {
			$categorizedData[$item->getCompetitionId()][$item->getTournamentId()][] = $item;
		}

		$i = 0;
		$result = [];
		foreach ($categorizedData as $competitionId => $competition) {
			$result[$i]['competition']['id'] = $competitionId;
			$j = 0;
			foreach ($competition as $tournamentId => $tournament) {
				$result[$i]['tournament'][$j]['id'] = $tournamentId;
				foreach ($tournament as $trophy) {
					$result[$i]['tournament'][$j]['season'] = $trophy->getTournamentSeason();
					$result[$i]['competition']['name'] = $this->client->translate($trophy->getCompetitionName(), $this->lang);
					$result[$i]['tournament'][$j][$trophy->getPosition()] = [
						'id' => $trophy->getTeamId(),
						'name' => $this->client->translate($trophy->getTeamName(), $this->lang),
					];
				}
				$j++;
			}
			$i++;
		}
		return $result;
	}
}