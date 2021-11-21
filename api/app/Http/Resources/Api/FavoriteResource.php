<?php


namespace App\Http\Resources\Api;


use App\Http\Resources\Api\Traits\CalculateResultTrait;
use App\Models\ReadModels\TeamsMatch;
use App\ValueObjects\Response\CompetitionResponse;
use App\ValueObjects\Response\MatchResponse;
use App\ValueObjects\Response\NameResponse;
use App\ValueObjects\Response\StageResponse;
use App\ValueObjects\Response\TeamFormSymbolsResponse;
use App\ValueObjects\Response\TeamResponse;
use App\ValueObjects\Response\TournamentResponse;
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
		$this->client = app(Client::class);
		$this->lang = app()->getLocale();
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
			'data' => array_filter([
				'nextMatch' => $this->makeNextMatchData(),
				'previousMatch' => $this->makeTeamFormData(),
				'teamFormSymbols' => $this->makeTeamFormSymbols()
			])
		];
	}

	/**
	 * @return array|array[]
	 */
	private function makeNextMatchData(): array
	{
		try {
			/** @var TeamsMatch $upcoming */
			$upcoming = $this->resource[TeamsMatch::STATUS_UPCOMING][0];

			[$matchStatus,] = explode('#', $upcoming->getSortKey());

			list($home, $away) = $this->checkHomeAwayTeam($upcoming);

			return MatchResponse::create(
				$upcoming->getMatchId(),
				$home,
				$away,
				TeamsMatch::getMatchDate($upcoming->getSortKey())->getTimestamp(),
				CompetitionResponse::create(
					$upcoming->getCompetitionId(),
					($upcoming->getCompetitionName()) ? $this->client->getByLang($upcoming->getCompetitionName(), $this->lang) : null
				),
				StageResponse::create($upcoming->getStageId()),
				TournamentResponse::create($upcoming->getTournamentId()),
				($matchStatus == TeamsMatch::STATUS_UPCOMING) ? 'notStarted' : null,
			)->toArray();
		} catch (Exception $exception) {
			return [];
		}
	}

	/**
	 * @return array
	 */
	private function makeTeamFormData(): array
	{
		try {
			/** @var TeamsMatch $finished */
			$finished = $this->resource[TeamsMatch::STATUS_FINISHED][0];

			list($home, $away) = $this->checkHomeAwayTeam($finished);

			[$matchStatus,] = explode('#', $finished->getSortKey());

			return MatchResponse::create(
					$finished->getMatchId(),
					$home,
					$away,
					TeamsMatch::getMatchDate($finished->getSortKey())->getTimestamp(),
					CompetitionResponse::create(
						$finished->getCompetitionId(),
						($finished->getCompetitionName()) ? $this->client->getByLang($finished->getCompetitionName(), $this->lang) : null
					),
					StageResponse::create($finished->getStageId()),
					TournamentResponse::create($finished->getTournamentId()),
				    $matchStatus,
					$finished->getCoverage(),
					$finished->getResult()
				)->toArray();

		} catch (Exception $exception) {
			return [];
		}
	}

	/**
	 * @return array
	 */
	private function makeTeamFormSymbols(): array
	{
		$form = array_map(function (TeamsMatch $teamsMatch) {
			return strtoupper($teamsMatch->getEvaluation()[0]);
		}, $this->resource[TeamsMatch::STATUS_FINISHED]);
		return TeamFormSymbolsResponse::create(
			TeamResponse::create(
				$this->resource['team']['id'],
				NameResponse::create(
					$this->client->getByLang($this->resource['team']['name']['original'], $this->lang),
					($this->resource['team']['name']['short']) ? $this->client->getByLang($this->resource['team']['name']['short'], $this->lang) : null,
					($this->resource['team']['name']['official']) ? $this->client->getByLang($this->resource['team']['name']['official'], $this->lang) : null
				)
			),
			$form
		)->toArray();
	}

	/**
	 * @param TeamsMatch $teamsMatch
	 * @return array
	 */
	private function checkHomeAwayTeam(TeamsMatch $teamsMatch): array
	{
		if ($teamsMatch->isHome()) {
			$home = TeamResponse::create(
				$teamsMatch->getTeamId(),
				NameResponse::create(
					$this->client->getByLang($teamsMatch->getTeamName()->getOriginal(), $this->lang),
					($teamsMatch->getTeamName()->getShort()) ? $this->client->getByLang($teamsMatch->getTeamName()->getShort(),
						$this->lang) : null
				)
			);
			$away = TeamResponse::create(
				$teamsMatch->getOpponentId(),
				NameResponse::create(
					$this->client->getByLang($teamsMatch->getOpponentName()->getOriginal(), $this->lang),
					($teamsMatch->getOpponentName()->getShort()) ? $this->client->getByLang($teamsMatch->getOpponentName()->getShort(),
						$this->lang) : null
				)
			);
		} else {
			$home = TeamResponse::create(
				$teamsMatch->getOpponentId(),
				NameResponse::create(
					$this->client->getByLang($teamsMatch->getOpponentName()->getOriginal(), $this->lang),
					($teamsMatch->getOpponentName()->getShort()) ? $this->client->getByLang($teamsMatch->getOpponentName()->getShort(),
						$this->lang) : null
				)
			);
			$away = TeamResponse::create(
				$teamsMatch->getTeamId(),
				NameResponse::create(
					$this->client->getByLang($teamsMatch->getTeamName()->getOriginal(), $this->lang),
					($teamsMatch->getTeamName()->getShort()) ? $this->client->getByLang($teamsMatch->getTeamName()->getShort(),
						$this->lang) : null
				)
			);
		}
		return array($home, $away);
	}
}