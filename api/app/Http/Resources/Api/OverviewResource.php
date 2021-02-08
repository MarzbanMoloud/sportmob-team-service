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
						'original' => $this->client->translate($this->resource['team']['name']['original'], $this->lang),
						'short' => $this->client->translate($this->resource['team']['name']['short'], $this->lang),
						'official' => $this->client->translate($this->resource['team']['name']['official'], $this->lang),
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
					'name' => $this->client->translate($upcoming->getCompetitionName(), $this->lang)
				],
				'team' => [
					'home' => [
						'id' => $upcoming->getTeamId(),
						'name' => [
							'original' => $this->client->translate($upcoming->getTeamName()->getOriginal(), $this->lang),
							'short' => $this->client->translate($upcoming->getTeamName()->getShort(), $this->lang),
						]
					],
					'away' => [
						'id' => $upcoming->getOpponentId(),
						'name' => [
							'original' => $this->client->translate($upcoming->getOpponentName()->getOriginal(), $this->lang),
							'short' => $this->client->translate($upcoming->getOpponentName()->getShort(), $this->lang)
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
							'original' => $this->client->translate($teamsMatch->getOpponentName()->getOriginal(), $this->lang),
							'short' => $this->client->translate($teamsMatch->getOpponentName()->getShort(), $this->lang),
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