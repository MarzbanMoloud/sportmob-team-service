<?php


namespace App\Models\ReadModels;


use App\ValueObjects\ReadModel\TeamName;
use App\Models\ReadModels\Traits\ReadModelTimestampTrait;
use App\Models\Repositories\DynamoDB\Interfaces\DynamoDBRepositoryModelInterface;


/**
 * Class Team
 * @package App\Models\ReadModels
 */
class Team implements DynamoDBRepositoryModelInterface
{
	use ReadModelTimestampTrait;

	public const TYPE_CLUB = 'club';
	public const TYPE_NATIONAL = 'national';

	private string $id;
	private string $country;
	private string $countryId;
	private ?string $city = null;
	private ?string $founded = null;
	private string $gender;
	private TeamName $name;
	private bool $active = true;
	private string $type;

	/**
	 * Team constructor.
	 */
	public function __construct()
	{
		$this->setUpdatedAt(new \DateTime());
	}

	/**
	 * @return string
	 */
	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * @param string $id
	 * @return Team
	 */
	public function setId(string $id): Team
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCountry(): string
	{
		return $this->country;
	}

	/**
	 * @param string $country
	 * @return Team
	 */
	public function setCountry(string $country): Team
	{
		$this->country = $country;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCountryId(): string
	{
		return $this->countryId;
	}

	/**
	 * @param string $countryId
	 * @return Team
	 */
	public function setCountryId(string $countryId): Team
	{
		$this->countryId = $countryId;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getCity(): ?string
	{
		return $this->city;
	}

	/**
	 * @param string|null $city
	 * @return Team
	 */
	public function setCity(?string $city): Team
	{
		$this->city = $city;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getFounded(): ?string
	{
		return $this->founded;
	}

	/**
	 * @param string|null $founded
	 * @return Team
	 */
	public function setFounded(?string $founded): Team
	{
		$this->founded = $founded;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getGender(): string
	{
		return $this->gender;
	}

	/**
	 * @param string $gender
	 * @return Team
	 */
	public function setGender(string $gender): Team
	{
		$this->gender = $gender;
		return $this;
	}

	/**
	 * @return TeamName
	 */
	public function getName(): TeamName
	{
		return $this->name;
	}

	/**
	 * @param TeamName $name
	 * @return Team
	 */
	public function setName(TeamName $name): Team
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isActive(): bool
	{
		return $this->active;
	}

	/**
	 * @param bool $active
	 * @return Team
	 */
	public function setActive(bool $active): Team
	{
		$this->active = $active;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getActive(): bool
	{
		return $this->active;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 * @return Team
	 */
	public function setType(string $type): Team
	{
		$this->type = $type;
		return $this;
	}
}