<?php


namespace App\ValueObjects\Response;


/**
 * Class FavoritePlayerResponse
 * @package App\ValueObjects\Response
 */
class FavoritePlayerResponse
{
	private PersonResponse $person;
	private ?TeamResponse $clubTeam;
	private ?TeamResponse $nationalTeam;
	private CompetitionResponse $competition;
	private array $stats;

    /**
     * @param PersonResponse $person
     * @param CompetitionResponse $competition
     * @param array $stats
     * @param TeamResponse|null $clubTeam
     * @param TeamResponse|null $nationalTeam
     * @return FavoritePlayerResponse
     */
	public static function create(
        PersonResponse $person,
        CompetitionResponse $competition,
        array $stats,
        ?TeamResponse $clubTeam =  null,
        ?TeamResponse $nationalTeam = null
	): FavoritePlayerResponse {
		$instance = new self();
		$instance->person = $person;
		$instance->competition = $competition;
		$instance->stats = $stats;
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
            'clubTeam' => $this->clubTeam ? $this->clubTeam->toArray() : null,
            'nationalTeam' => $this->nationalTeam ? $this->nationalTeam->toArray() : null,
            'stats' => $this->stats,
        ]);
    }
}
