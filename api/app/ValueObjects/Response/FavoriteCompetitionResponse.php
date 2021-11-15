<?php


namespace App\ValueObjects\Response;


/**
 * Class FavoriteCompetitionResponse
 * @package App\ValueObjects\Response
 */
class FavoriteCompetitionResponse
{
	private CompetitionResponse $competition;
	private ?TeamResponse $team = null;
	private ?string $season = null;
	private ?string $startDate = null;
	private ?int $mostPoints = null;
	private ?int $matches = null;
	/**
	 * @var FavoriteCompetitionStatsResponse[]
	 */
	private ?array $stats = null;

	/**
	 * @param CompetitionResponse $competition
	 * @param TeamResponse|null $team
	 * @param string|null $season
	 * @param string|null $startDate
	 * @param int|null $mostPoints
	 * @param int|null $matches
	 * @param array|null $stats
	 * @return FavoriteCompetitionResponse
	 */
	public static function create(
		CompetitionResponse $competition,
		?TeamResponse $team = null,
		?string $season = null,
		?string $startDate = null,
		?int $mostPoints = null,
		?int $matches = null,
		?array $stats = null
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
    		'team' => $this->team ? $this->team->toArray() : null,
    		'season' => $this->season,
    		'startDate' => $this->startDate,
    		'mostPoints' => $this->mostPoints,
    		'matches' => $this->matches,
    		'stats' => $this->stats,
        ]);
    }
}
