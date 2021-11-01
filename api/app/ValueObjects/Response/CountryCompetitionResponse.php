<?php


namespace App\ValueObjects\Response;


/**
 * Class CountryCompetitionResponse
 * @package App\ValueObjects\Response
 */
class CountryCompetitionResponse
{
    private CountryResponse $country;
    private ?array $competitions = null;

	/**
	 * @param CountryResponse $country
	 * @param array|null $competitions
	 * @return CountryCompetitionResponse
	 */
    public static function create(
		CountryResponse $country,
		?array $competitions = null
	): CountryCompetitionResponse {
        $instance = new self();
        $instance->country = $country;
        $instance->competitions = $competitions;
        return $instance;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_filter([
            'country' => $this->country->toArray(),
            'competitions' => $this->competitions
        ]);
    }
}
