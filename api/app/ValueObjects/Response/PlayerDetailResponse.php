<?php


namespace App\ValueObjects\Response;


/**
 * Class PlayerDetailResponse
 * @package App\ValueObjects\Response
 */
class PlayerDetailResponse
{
	private PersonResponse $person;
	private ?TeamResponse $clubTeam = null;
	private ?TeamResponse $nationalTeam = null;
	private ?CountryResponse $country = null;
	private ?string $preferredFoot = null;
	private ?int $clubShirtNo = null;
	private ?int $nationalShirtNo = null;
	private ?float $marketValue = null;
	private ?float $averageRate = null;

    /**
     * @param PersonResponse $person
     * @param TeamResponse|null $clubTeam
     * @param TeamResponse|null $nationalTeam
     * @param CountryResponse|null $country
     * @param string|null $preferredFoot
     * @param int|null $clubShirtNo
     * @param int|null $nationalShirtNo
     * @param float|null $marketValue
     * @param float|null $averageRate
     * @return PlayerDetailResponse
     */
	public static function create(
		PersonResponse $person,
        ?TeamResponse $clubTeam = null,
        ?TeamResponse $nationalTeam = null,
        ?CountryResponse $country = null,
        ?string $preferredFoot = null,
        ?int $clubShirtNo = null,
        ?int $nationalShirtNo = null,
        ?float $marketValue = null,
        ?float $averageRate = null
	): PlayerDetailResponse {
		$instance = new self();
		$instance->person = $person;
		$instance->clubTeam = $clubTeam;
		$instance->nationalTeam = $nationalTeam;
		$instance->country = $country;
		$instance->preferredFoot = $preferredFoot;
		$instance->clubShirtNo = $clubShirtNo;
		$instance->nationalShirtNo = $nationalShirtNo;
		$instance->marketValue = $marketValue;
		$instance->averageRate = $averageRate;
		return $instance;
	}

	/**
	 * @return array
	 */
    public function toArray(): array
	{
        return array_filter([
            'person' => $this->person->toArray(),
            'clubTeam' => $this->clubTeam ? $this->clubTeam->toArray() : null,
            'nationalTeam' => $this->nationalTeam ? $this->nationalTeam->toArray() : null,
            'country' => $this->country ? $this->country->toArray() : null,
            'preferredFoot' => $this->preferredFoot,
            'clubShirtNo' => $this->clubShirtNo,
            'nationalShirtNo' => $this->nationalShirtNo,
            'marketValue' => $this->marketValue,
            'averageRate' => $this->averageRate,
        ]);
    }
}
