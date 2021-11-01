<?php


namespace App\ValueObjects\Response;


/**
 * Class FavoriteCompetitionStatsResponse
 * @package App\ValueObjects\Response
 */
class FavoriteCompetitionStatsResponse
{
    private string $key;
    private ?string $value = null;
    private ?string $clubTeamName = null;
    private ?PersonResponse $person = null;

	/**
	 * @param string $key
	 * @param string|null $value
	 * @param string|null $clubTeamName
	 * @param PersonResponse|null $person
	 * @return FavoriteCompetitionStatsResponse
	 */
    public static function create(
    	string $key,
		?string $value = null,
		?string $clubTeamName = null,
		?PersonResponse $person = null
	): FavoriteCompetitionStatsResponse {
        $instance = new self();
        $instance->key = $key;
        $instance->value = $value;
        $instance->clubTeamName = $clubTeamName;
        $instance->person = $person;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
            'key' => $this->key,
            'value' => $this->value,
            'clubTeamName' => $this->clubTeamName,
            'person' => $this->person ? $this->person->toArray() : null,
        ]);
    }
}
