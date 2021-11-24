<?php


namespace App\ValueObjects\Response;


/**
 * Class MatchResponse
 * @package App\ValueObjects\Response
 */
class MatchResponse
{
	private string $id;
    private CompetitionResponse $competition;
    private TournamentResponse $tournament;
    private StageResponse $stage;
	private TeamResponse $homeTeam;
	private TeamResponse $awayTeam;
	private int $date;
	private string $status;
	private ?string $coverage = null;
	private ?ResultResponse $result = null;
	private ?TimeStatusResponse $timeStatus = null;
	private ?string $venue = null;
    /**
     * @var PersonResponse[]|null
     */
	private ?array $referees = null;
    /**
     * @var IncidentResponse[]|null
     */
	private ?array $incidents = null;

	/**
	 * @param string $id
	 * @param TeamResponse $homeTeam
	 * @param TeamResponse $awayTeam
	 * @param int $date
	 * @param CompetitionResponse $competition
	 * @param StageResponse $stage
	 * @param TournamentResponse $tournament
	 * @param string $status
	 * @param string|null $coverage
	 * @param ResultResponse|null $result
	 * @param TimeStatusResponse|null $timeStatus
	 * @param string|null $venue
	 * @param array|null $referees
	 * @param array|null $incidents
	 * @return MatchResponse
	 */
	public static function create(
		string $id,
		TeamResponse $homeTeam,
		TeamResponse $awayTeam,
		int $date,
		CompetitionResponse $competition,
		StageResponse $stage,
		TournamentResponse $tournament,
		string $status,
		?string $coverage = null,
		?ResultResponse $result = null,
		?TimeStatusResponse $timeStatus = null,
		?string $venue = null,
		?array $referees = null,
		?array $incidents = null
	): MatchResponse {
		$instance = new self();
		$instance->id = $id;
		$instance->homeTeam = $homeTeam;
		$instance->awayTeam = $awayTeam;
		$instance->date = $date;
		$instance->competition = $competition;
        $instance->stage = $stage;
        $instance->tournament = $tournament;
        $instance->status = $status;
		$instance->coverage = $coverage;
		$instance->result = $result;
		$instance->timeStatus = $timeStatus;
		$instance->venue = $venue;
		$instance->referees = $referees;
		$instance->incidents = $incidents;
		return $instance;
	}

	/**
	 * @return array
	 */
    public function toArray(): array
	{
        return array_filter([
            'id' => $this->id,
            'competition' => $this->competition->toArray(),
            'tournament' => $this->tournament->toArray(),
            'stage' => $this->stage->toArray(),
            'homeTeam' => $this->homeTeam ? $this->homeTeam->toArray() : null,
            'awayTeam' => $this->awayTeam ? $this->awayTeam->toArray() : null,
            'status' => $this->status,
            'date' => $this->date,
			'coverage' => $this->coverage,
			'result' => $this->result ? $this->result->toArray() : null,
			'timeStatus' => $this->timeStatus ? $this->timeStatus->toArray() : null,
			'venue' => $this->venue,
			'referees' => $this->referees,
			'incidents' => $this->incidents,
        ]);
    }
}
