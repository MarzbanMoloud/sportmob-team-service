<?php


namespace App\Http\Resources\Api;


use App\Http\Resources\Api\Traits\CalculateResultTrait;
use App\Models\ReadModels\TeamsMatch;
use Exception;
use Illuminate\Http\Resources\Json\JsonResource;
use SportMob\Translation\Client;


/**
 * Class FavoriteResource
 * @package App\Http\Resources\Api
 */
class FavoriteResource extends JsonResource
{
	use CalculateResultTrait;

	private Client $client;
	private string $lang;

	/**
	 * FavoriteResource constructor.
	 * @param $resource
	 */
	public function __construct($resource)
	{
		$this->client = app('TranslationClient');
		$this->lang = app()->getLocale();
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
				'lastMatches' => array_map(function (TeamsMatch $teamsMatch) {
					return $teamsMatch->getEvaluation();
				}, $this->resource[TeamsMatch::STATUS_FINISHED])
			]
		];
	}

	/**
	 * @return array|array[]
	 */
	private function makeUpcomingData(): array
	{
		try {
			/** @var TeamsMatch $upcoming */
			$upcoming = $this->resource[TeamsMatch::STATUS_UPCOMING][0];
			return [
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
					],
				],
				'date' => TeamsMatch::getMatchDate($upcoming->getSortKey())->getTimestamp(),
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
			/** @var TeamsMatch $finished */
			$finished = $this->resource[TeamsMatch::STATUS_FINISHED][0];
			return[
				'team' => [
					'id' => $finished->getTeamId(),
					'name' => [
						'original' => $this->client->translate($finished->getOpponentName()->getOriginal(), $this->lang),
						'short' => $this->client->translate($finished->getOpponentName()->getShort(), $this->lang),
					]
				],
				'date' => TeamsMatch::getMatchDate($finished->getSortKey())->getTimestamp(),
				'result' => $this->getResult($finished->getResult())
			];
		} catch (Exception $exception) {
			return [];
		}
	}
}