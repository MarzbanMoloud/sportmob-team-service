<?php


namespace App\ValueObjects\Response;


/**
 * Class FavoriteCompetitionResponse
 * @package App\ValueObjects\Response
 */
class FavoriteCompetitionResponse
{
	private CompetitionResponse $competition;
	private string $season;
	private string $startDate;
	private ?TopStatTeamResponse $topTeam = null;
	private ?TopStatPersonResponse $topScorer = null;
	private ?TopStatPersonResponse $topAssist = null;

	/**
	 * @param CompetitionResponse $competition
	 * @param string $season
	 * @param string $startDate
	 * @param TopStatTeamResponse|null $topTeam
	 * @param TopStatPersonResponse|null $topScorer
	 * @param TopStatPersonResponse|null $topAssist
	 * @return FavoriteCompetitionResponse
	 */
	public static function create(
		CompetitionResponse $competition,
		string $season,
		string $startDate,
		?TopStatTeamResponse $topTeam = null,
		?TopStatPersonResponse $topScorer = null,
		?TopStatPersonResponse $topAssist = null
	): FavoriteCompetitionResponse {
		$instance = new self();
		$instance->competition = $competition;
		$instance->season = $season;
		$instance->startDate = $startDate;
		$instance->topTeam = $topTeam;
		$instance->topScorer = $topScorer;
		$instance->topAssist = $topAssist;
		return $instance;
	}

	/**
	 * @return array
	 */
    public function toArray(): array
	{
        return array_filter([
    		'competition' => $this->competition->toArray(),
			'season' => $this->season,
			'startDate' => $this->startDate,
    		'topTeam' => $this->topTeam ? $this->topTeam->toArray() : null,
    		'topScorer' => $this->topScorer ? $this->topScorer->toArray() : null,
    		'topAssist' => $this->topAssist ? $this->topAssist->toArray() : null,
        ]);
    }
}
