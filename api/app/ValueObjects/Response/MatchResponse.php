<?php


namespace App\ValueObjects\Response;


/**
 * Class MatchResponse
 * @package App\ValueObjects\Response
 */
class MatchResponse
{
	private string $id;
	private TeamResponse $homeTeam;
	private TeamResponse $awayTeam;
	private int $date;
	private ?CompetitionResponse $competition = null;
	private ?string $coverage = null;
	private ?array $result = null;//TODO:: change!
	private ?string $currentStatus = null;
	private ?string $status = null;
	private ?MatchStatusResponse $statuses = null;
	private ?string $stage = null;
	private ?string $venue = null;
	private ?array $referees = null;
	private ?int $priority = null;
	private ?array $incidents = null;
	private ?TeamFormSymbols $homeTeamFormSymbols = null;
	private ?TeamFormSymbols $awayTeamFormSymbols = null;
	private ?array $ranking = null;

	/**
	 * @param string $id
	 * @param TeamResponse $homeTeam
	 * @param TeamResponse $awayTeam
	 * @param int $date
	 * @param CompetitionResponse|null $competition
	 * @param string|null $coverage
	 * @param array|null $result
	 * @param string|null $currentStatus
	 * @param string|null $status
	 * @param MatchStatusResponse|null $statuses
	 * @param string|null $stage
	 * @param string|null $venue
	 * @param array|null $referees
	 * @param int|null $priority
	 * @param array|null $incidents
	 * @param TeamFormSymbols|null $homeTeamFormSymbols
	 * @param TeamFormSymbols|null $awayTeamFormSymbols
	 * @param array|null $ranking
	 * @return MatchResponse
	 */
	public static function create(
		string $id,
		TeamResponse $homeTeam,
		TeamResponse $awayTeam,
		int $date,
		?CompetitionResponse $competition = null,
		?string $coverage = null,
		?array $result = null,
		?string $currentStatus = null,
		?string $status = null,
		?MatchStatusResponse $statuses = null,
		?string $stage = null,
		?string $venue = null,
		?array $referees = null,
		?int $priority = null,
		?array $incidents = null,
		?TeamFormSymbols $homeTeamFormSymbols = null,
		?TeamFormSymbols $awayTeamFormSymbols = null,
		?array $ranking = null
	): MatchResponse {
		$instance = new self();
		$instance->id = $id;
		$instance->homeTeam = $homeTeam;
		$instance->awayTeam = $awayTeam;
		$instance->date = $date;
		$instance->competition = $competition;
		$instance->coverage = $coverage;
		$instance->result = $result;
		$instance->currentStatus = $currentStatus;
		$instance->status = $status;
		$instance->statuses = $statuses;
		$instance->stage = $stage;
		$instance->venue = $venue;
		$instance->referees = $referees;
		$instance->priority = $priority;
		$instance->incidents = $incidents;
		$instance->homeTeamFormSymbols = $homeTeamFormSymbols;
		$instance->awayTeamFormSymbols = $awayTeamFormSymbols;
		$instance->ranking = $ranking;
		return $instance;
	}

	/**
	 * @return array
	 */
    public function toArray(): array
	{
        return array_filter([
            'id' => $this->id,
            'homeTeam' => $this->homeTeam ? $this->homeTeam->toArray() : null,
            'awayTeam' => $this->awayTeam ? $this->awayTeam->toArray() : null,
            'competition' => $this->competition ? $this->competition->toArray() : null,
            'date' => $this->date,
			'coverage' => $this->coverage,
			'result' => $this->result,
			'currentStatus' => $this->currentStatus,
			'status' => $this->status,
			'statuses' => $this->statuses ? $this->statuses->toArray() : null,
			'stage' => $this->stage,
			'venue' => $this->venue,
			'referees' => $this->referees,
			'priority' => $this->priority,
			'incidents' => $this->incidents,
			'homeTeamFormSymbols' => $this->homeTeamFormSymbols ? $this->homeTeamFormSymbols->toArray() : null,
			'awayTeamFormSymbols' => $this->awayTeamFormSymbols ? $this->awayTeamFormSymbols->toArray() : null,
			'ranking' => $this->ranking,
        ]);
    }
}
