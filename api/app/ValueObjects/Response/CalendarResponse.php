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
    private array $matches;

	/**
	 * @param CompetitionResponse $competition
	 * @param TournamentResponse $tournament
	 * @param CountryResponse $country
	 * @param array $matches
	 * @return CalendarResponse
	 */
    public static function create(
		CompetitionResponse $competition,
		TournamentResponse $tournament,
		CountryResponse $country,
		array $matches
	): CalendarResponse {
        $instance = new self();
        $instance->competition = $competition;
        $instance->tournament = $tournament;
        $instance->country = $country;
        $instance->matches = $matches;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'competition' => $this->competition->toArray(),
            'tournament' => $this->tournament->toArray(),
            'country' => $this->country->toArray(),
            'matches' => $this->matches,
        ];
    }
}
