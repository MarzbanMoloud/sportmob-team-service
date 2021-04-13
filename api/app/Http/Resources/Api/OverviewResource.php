<?php


namespace App\Http\Resources\Api;


use App\Http\Resources\Api\Traits\CalculateResultTrait;
use App\Models\ReadModels\TeamsMatch;
use Exception;
use Illuminate\Http\Resources\Json\JsonResource;
use SportMob\Translation\Client;


/**
 * Class OverviewResource
 * @package App\Http\Resources\Api
 */
class OverviewResource extends JsonResource
{
	use CalculateResultTrait;

	private Client $client;
	private string $lang;

	/**
	 * OverviewResource constructor.
	 * @param $resource
	 */
	public function __construct($resource)
	{
		$this->client = app(Client::class);
		$this->lang = app()->getLocale();
		parent::__construct($resource);
	}

	/**
	 * @param $data
	 * @return array|array[]
	 */
	public function toArray($data): array
	{
		return [
			'links' => [],
			'data' => [
				'team' => [
					'id' => $this->resource['team']['id'],
					'name' => [
						'original' => ($this->resource['team']['name']['original']) ? $this->client->getByLang($this->resource['team']['name']['original'], $this->lang) : null,
						'short' => ($this->resource['team']['name']['short']) ? $this->client->getByLang($this->resource['team']['name']['short'], $this->lang) : null,
						'official' => ($this->resource['team']['name']['official']) ? $this->client->getByLang($this->resource['team']['name']['official'], $this->lang) : null,
					]
				],
				TeamsMatch::STATUS_UPCOMING => $this->makeUpcomingData(),
				TeamsMatch::STATUS_FINISHED => $this->makeFinishedData(),
			]
		];
	}

	/**
	 * @return array
	 */
	private function makeUpcomingData(): array
	{
		try {
			/**
			 * @var TeamsMatch $upcoming
			 */
			$upcoming = $this->resource['upcoming'][0];
			return [
				'competition' => [
					'id' => $upcoming->getCompetitionId(),
					'name' => ($upcoming->getCompetitionName()) ? $this->client->getByLang($upcoming->getCompetitionName(), $this->lang) : null
				],
				'team' => [
					'home' => [
						'id' => $upcoming->getTeamId(),
						'name' => [
							'original' => ($upcoming->getTeamName()->getOriginal()) ? $this->client->getByLang($upcoming->getTeamName()->getOriginal(), $this->lang) : null,
							'short' => ($upcoming->getTeamName()->getShort()) ? $this->client->getByLang($upcoming->getTeamName()->getShort(), $this->lang) : null,
						]
					],
					'away' => [
						'id' => $upcoming->getOpponentId(),
						'name' => [
							'original' => ($upcoming->getOpponentName()->getOriginal()) ? $this->client->getByLang($upcoming->getOpponentName()->getOriginal(), $this->lang) : null,
							'short' => ($upcoming->getOpponentName()->getShort()) ? $this->client->getByLang($upcoming->getOpponentName()->getShort(), $this->lang) : null
						]
					]
				],
				'date' => TeamsMatch::getMatchDate($upcoming->getSortKey())->getTimestamp(),
				'coverage' => $upcoming->getCoverage()
			];
		} catch (Exception $exception) {
			return [];
		}
	}

	/**
	 * @return array
	 */
	private function makeFinishedData(): array
	{
		try {
			return array_map(function (TeamsMatch $teamsMatch) {
				return [
					'team' => [
						'id' => $teamsMatch->getOpponentId(),
						'name' => [
							'original' => ($teamsMatch->getOpponentName()->getOriginal()) ? $this->client->getByLang($teamsMatch->getOpponentName()->getOriginal(), $this->lang) : null,
							'short' => ($teamsMatch->getOpponentName()->getShort()) ? $this->client->getByLang($teamsMatch->getOpponentName()->getShort(), $this->lang) : null,
						]
					],
					'date' => TeamsMatch::getMatchDate($teamsMatch->getSortKey())->getTimestamp(),
					'result' => $this->getResult($teamsMatch->getResult())
				];
			}, $this->resource['finished']);
		} catch (Exception $exception) {
			return [];
		}
	}
}