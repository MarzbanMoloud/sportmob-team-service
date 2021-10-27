<?php


namespace App\ValueObjects\Response;


/**
 * Class CompetitionTournamentResponse
 * @package App\ValueObjects\Response
 */
class CompetitionTournamentResponse
{
	private CompetitionResponse $competition;
	private TournamentResponse $tournament;

    /**
     * @param CompetitionResponse $competition
     * @param TournamentResponse $tournament
     * @return CompetitionTournamentResponse
     */
	public static function create(
		CompetitionResponse $competition,
		TournamentResponse $tournament
	): CompetitionTournamentResponse {
		$instance = new self();
		$instance->competition = $competition;
		$instance->tournament = $tournament;
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
        ]);
    }
}
