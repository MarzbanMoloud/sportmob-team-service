<?php


namespace App\ValueObjects\Response;


/**
 * Class FavoriteCompetitionResponse
 * @package App\ValueObjects\Response
 */
class FavoriteCompetitionResponse
{
	private CompetitionResponse $competition;
	private TeamResponse $team;
	private ?string $season = null;
	private ?string $startDate = null;
	private ?int $mostPoints = null;
	private ?int $matches = null;
	private ?FavoriteCompetitionStatsResponse $stats = null;


	/**
	 * @param CompetitionResponse $competition
	 * @param TeamResponse $team
	 * @param string|null $season
	 * @param string|null $startDate
	 * @param int|null $mostPoints
	 * @param int|null $matches
	 * @param FavoriteCompetitionStatsResponse|null $stats
	 * @return FavoriteCompetitionResponse
	 */
	public static function create(
		CompetitionResponse $competition,
		TeamResponse $team,
		?string $season = null,
		?string $startDate = null,
		?int $mostPoints = null,
		?int $matches = null,
		?FavoriteCompetitionStatsResponse $stats = null
	): FavoriteCompetitionResponse {
		$instance = new self();
		$instance->competition = $competition;
		$instance->team = $team;
		$instance->season = $season;
		$instance->startDate = $startDate;
		$instance->mostPoints = $mostPoints;
		$instance->matches = $matches;
		$instance->stats = $stats;
		return $instance;
	}

	/**
	 * @return array
	 */
    public function toArray(): array
	{
        return array_filter([
    		'competition' => $this->competition->toArray(),
    		'team' => $this->team->toArray(),
    		'season' => $this->season,
    		'startDate' => $this->startDate,
    		'mostPoints' => $this->mostPoints,
    		'matches' => $this->matches,
    		'stats' => $this->stats ? $this->stats->toArray() : null,
        ]);
    }
}
