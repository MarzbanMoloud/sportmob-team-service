<?php


namespace App\ValueObjects\Response;


/**
 * Class TopStatPersonResponse
 * @package App\ValueObjects\Response
 */
class TopStatPersonResponse
{
	private PersonResponse $person;
	private string $clubTeamName;
	private ?StatItemResponse $stat = null;

	/**
	 * @param PersonResponse $person
	 * @param string $clubTeamName
	 * @param StatItemResponse|null $stat
	 * @return TopStatPersonResponse
	 */
    public static function create(
		PersonResponse $person,
		string $clubTeamName,
		?StatItemResponse $stat = null
    ): TopStatPersonResponse {
        $instance = new self();
        $instance->person = $person;
        $instance->clubTeamName = $clubTeamName;
        $instance->stat = $stat;
        return $instance;
    }

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return array_filter([
			'person' => $this->person->toArray(),
			'clubTeamName' => $this->clubTeamName,
			'stat' => $this->stat ? $this->stat->toArray() : null,
		]);
	}
}
