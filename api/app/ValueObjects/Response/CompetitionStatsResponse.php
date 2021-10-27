<?php


namespace App\ValueObjects\Response;


/**
 * Class CompetitionStatsResponse
 * @package App\ValueObjects\Response
 */
class CompetitionStatsResponse
{
	private PersonResponse $person;
	private CompetitionResponse $competition;
	private int $stat;
	private ?TeamResponse $clubTeam = null;
	private ?TeamResponse $nationalTeam = null;

    /**
     * @param PersonResponse $person
     * @param CompetitionResponse $competition
     * @param int $stat
     * @param TeamResponse|null $clubTeam
     * @param TeamResponse|null $nationalTeam
     * @return CompetitionStatsResponse
     */
	public static function create(
        PersonResponse $person,
        CompetitionResponse $competition,
        int $stat,
        ?TeamResponse $clubTeam = null,
        ?TeamResponse $nationalTeam = null
	): CompetitionStatsResponse {
		$instance = new self();
		$instance->person = $person;
		$instance->competition = $competition;
		$instance->stat = $stat;
		$instance->clubTeam = $clubTeam;
		$instance->nationalTeam = $nationalTeam;
		return $instance;
	}

	/**
	 * @return array
	 */
    public function toArray(): array
	{
        return array_filter([
            'person' => $this->person->toArray(),
            'competition' => $this->competition->toArray(),
            'stat' => $this->stat,
            'clubTeam' => $this->clubTeam ? $this->clubTeam->toArray() : null,
            'nationalTeam' => $this->nationalTeam ? $this->nationalTeam->toArray() : null,
        ]);
    }
}
