<?php


namespace App\ValueObjects\Response;


/**
 * Class CompetitionResponse
 * @package App\ValueObjects\Response
 */
class CompetitionResponse
{
	private string $id;
	private ?string $name = null;
	private ?CountryResponse $country = null;
	private ?string $format = null;
	private ?bool $isYouth = null;
	private ?bool $isClub = null;
	private ?bool $isFriendly = null;
	private ?bool $isInternational = null;
	private ?int $priority = null;

	/**
	 * @param string $id
	 * @param string|null $name
	 * @param CountryResponse|null $country
	 * @param string|null $format
	 * @param bool|null $isYouth
	 * @param bool|null $isClub
	 * @param bool|null $isFriendly
	 * @param bool|null $isInternational
	 * @param int|null $priority
	 * @return CompetitionResponse
	 */
	public static function create(
		string $id,
		?string $name = null,
		?CountryResponse $country = null,
		?string $format = null,
		?bool $isYouth = null,
		?bool $isClub = null,
		?bool $isFriendly = null,
		?bool $isInternational = null,
		?int $priority = null
	): CompetitionResponse {
		$instance = new self();
		$instance->id = $id;
		$instance->name = $name;
		$instance->country = $country;
		$instance->format = $format;
		$instance->isYouth = $isYouth;
		$instance->isClub = $isClub;
		$instance->isFriendly = $isFriendly;
		$instance->isInternational = $isInternational;
		$instance->priority = $priority;
		return $instance;
	}

	/**
	 * @return array
	 */
    public function toArray(): array
	{
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'country' => $this->country ? $this->country->toArray() : null,
            'format' => $this->format,
            'isYouth' => $this->isYouth,
            'isClub' => $this->isClub,
            'isFriendly' => $this->isFriendly,
            'isInternational' => $this->isInternational,
            'priority' => $this->priority,
        ]);
    }
}
