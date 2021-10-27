<?php


namespace App\ValueObjects\Response;


/**
 * Class SquadResponse
 * @package App\ValueObjects\Response
 */
class SquadResponse
{
	private PersonResponse $person;
	private ?TeamResponse $nationalTeam = null;
	private ?TeamResponse $clubTeam = null;

    /**
     * @param PersonResponse $person
     * @param TeamResponse|null $nationalTeam
     * @param TeamResponse|null $clubTeam
     * @return SquadResponse
     */
	public static function create(
        PersonResponse $person,
        ?TeamResponse $nationalTeam = null,
        ?TeamResponse $clubTeam = null
	): SquadResponse {
		$instance = new self();
        $instance->person = $person;
        $instance->nationalTeam = $nationalTeam;
        $instance->clubTeam = $clubTeam;
		return $instance;
	}

	/**
	 * @return array
	 */
    public function toArray(): array
	{
        return array_filter([
            'person' => $this->person->toArray(),
            'nationalTeam' => $this->nationalTeam ? $this->nationalTeam->toArray() : null,
            'clubTeam' => $this->clubTeam ? $this->clubTeam->toArray() : null,
        ]);
    }
}
