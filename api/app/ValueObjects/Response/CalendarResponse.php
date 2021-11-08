<?php


namespace App\ValueObjects\Response;


/**
 * Class CalendarResponse
 * @package App\ValueObjects\Response
 */
class CalendarResponse
{
    private CompetitionResponse $competition;
    private TournamentResponse $tournament;
    private CountryResponse $country;
    private ?int $priority;
    /**
     * @var MatchCalendarResponse[]
     */
    private array $matches;

	/**
	 * @param CompetitionResponse $competition
	 * @param TournamentResponse $tournament
	 * @param CountryResponse $country
     * @param int $priority
	 * @param array $matches
	 * @return CalendarResponse
	 */
    public static function create(
		CompetitionResponse $competition,
		TournamentResponse $tournament,
		CountryResponse $country,
		int $priority,
		array $matches
	): CalendarResponse {
        $instance = new self();
        $instance->competition = $competition;
        $instance->tournament = $tournament;
        $instance->country = $country;
        $instance->priority = $priority;
        $instance->matches = $matches;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
            'competition' => $this->competition->toArray(),
            'tournament' => $this->tournament->toArray(),
            'country' => $this->country->toArray(),
            'priority' => $this->priority,
            'matches' => $this->matches,
        ]);
    }
}
