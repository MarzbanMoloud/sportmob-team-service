<?php


namespace App\Http\Resources\Api;


use App\Http\Resources\Api\Traits\CalculateResultTrait;
use App\Models\ReadModels\TeamsMatch;
use App\ValueObjects\Response\CompetitionResponse;
use App\ValueObjects\Response\CountryResponse;
use App\ValueObjects\Response\HomeAwayResponse;
use App\ValueObjects\Response\MatchResponse;
use App\ValueObjects\Response\NameResponse;
use App\ValueObjects\Response\ResultResponse;
use App\ValueObjects\Response\StageResponse;
use App\ValueObjects\Response\TeamFormResponse;
use App\ValueObjects\Response\TeamOverviewResponse;
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
			'data' => TeamOverviewResponse::create(
				$this->makeNextMatchData(),
				$this->makeTeamFormData()
			)->toArray()
		];
	}

	/**
	 * @return MatchResponse
	 */
	private function makeNextMatchData(): ?MatchResponse
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
					$this->client->getByLang($upcoming->getCompetitionName(), $this->lang),
					CountryResponse::create(
						$upcoming->getCountryId(),
						$upcoming->getCountryName()
					)
				),
				StageResponse::create($upcoming->getStageId()),
				TournamentResponse::create($upcoming->getTournamentId()),
				($matchStatus == TeamsMatch::STATUS_UPCOMING) ? 'notStarted' : null,
				$upcoming->getCoverage()
			);

		} catch (Exception $exception) {
			return null;
		}
	}

	/**
	 * @return TeamFormResponse
	 */
	private function makeTeamFormData(): ?TeamFormResponse
	{
		try {
			$form = array_map(function (TeamsMatch $teamsMatch) {
				list($home, $away) = $this->checkHomeAwayTeam($teamsMatch);

				[$matchStatus,] = explode('#', $teamsMatch->getSortKey());

				$resultResponse = ResultResponse::create(
					isset($teamsMatch->getResult()['total']) ? HomeAwayResponse::create(
						$teamsMatch->getResult()['total']['home'], $teamsMatch->getResult()['total']['away']) : null,
					isset($teamsMatch->getResult()['penalty']) ? HomeAwayResponse::create(
						$teamsMatch->getResult()['total']['penalty'], $teamsMatch->getResult()['penalty']['away']) : null
				);

				return MatchResponse::create(
					$teamsMatch->getMatchId(),
					$home,
					$away,
					TeamsMatch::getMatchDate($teamsMatch->getSortKey())->getTimestamp(),
					CompetitionResponse::create(
						$teamsMatch->getCompetitionId(),
						$this->client->getByLang($teamsMatch->getCompetitionName(), $this->lang),
						CountryResponse::create(
							$teamsMatch->getCountryId(),
							$teamsMatch->getCountryName()
						)
					),
					StageResponse::create($teamsMatch->getStageId()),
					TournamentResponse::create($teamsMatch->getTournamentId()),
					$matchStatus,
					$teamsMatch->getCoverage(),
					$resultResponse
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
			);

		} catch (Exception $exception) {
			return null;
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