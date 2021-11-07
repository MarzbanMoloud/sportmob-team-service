<?php


namespace App\Http\Resources\Api;


use App\Http\Resources\Api\Traits\CalculateResultTrait;
use App\Models\ReadModels\TeamsMatch;
use App\ValueObjects\Response\CompetitionResponse;
use App\ValueObjects\Response\MatchResponse;
use App\ValueObjects\Response\NameResponse;
use App\ValueObjects\Response\StageResponse;
use App\ValueObjects\Response\TeamForm;
use App\ValueObjects\Response\TeamFormResponse;
use App\ValueObjects\Response\TeamResponse;
use App\ValueObjects\Response\TournamentResponse;
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
	 * @param $resource
	 * @return array|array[]
	 */
	public function toArray($resource): array
	{
		return [
			'links' => (object) null,
			'data' => [
				'nextMatch' => $this->makeNextMatchData(),
				'teamForm' => $this->makeTeamFormData(),
			]
		];
	}

	/**
	 * @return array
	 */
	private function makeNextMatchData(): array
	{
		try {
			/** @var TeamsMatch $upcoming */
			$upcoming = $this->resource['upcoming'][0];

			list($home, $away) = $this->checkHomeAwayTeam($upcoming);

			[$matchStatus,] = explode('#', $upcoming->getSortKey());

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
				$upcoming->getCoverage()
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
			$form = array_map(function (TeamsMatch $teamsMatch) {
				list($home, $away) = $this->checkHomeAwayTeam($teamsMatch);

				[$matchStatus,] = explode('#', $teamsMatch->getSortKey());

				return MatchResponse::create(
					$teamsMatch->getMatchId(),
					$home,
					$away,
					TeamsMatch::getMatchDate($teamsMatch->getSortKey())->getTimestamp(),
					CompetitionResponse::create(
						$teamsMatch->getCompetitionId(),
						($teamsMatch->getCompetitionName()) ? $this->client->getByLang($teamsMatch->getCompetitionName(), $this->lang) : null
					),
					StageResponse::create($teamsMatch->getStageId()),
					TournamentResponse::create($teamsMatch->getTournamentId()),
					$matchStatus,
					$teamsMatch->getCoverage(),
					$teamsMatch->getResult()
				)->toArray();

			}, $this->resource['finished']);

			return TeamFormResponse::create(
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

		} catch (Exception $exception) {
			return [];
		}
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