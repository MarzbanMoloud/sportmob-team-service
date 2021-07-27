<?php


namespace App\ValueObjects\DTO;


/**
 * Class TransferDTO
 * @package App\ValueObjects\DTO
 */
class TransferDTO
{
	public string $id;
	public string $personId;
	public string $personName;
	public string $teamToId;
	public string $teamToName;
	public ?string $teamFromId = null;
	public ?string $teamFromName = null;
	public ?string $marketValue = null;
	public ?string $startDate = null;
	public ?string $endDate = null;
	public ?string $announcedDate = null;
	public ?string $contractDate = null;
	public int $like;
	public int $dislike;
	public ?string $season = null;
	public string $type;

	/**
	 * @return string
	 */
	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * @param string $id
	 * @return TransferDTO
	 */
	public function setId(string $id): TransferDTO
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPersonId(): string
	{
		return $this->personId;
	}

	/**
	 * @param string $personId
	 * @return TransferDTO
	 */
	public function setPersonId(string $personId): TransferDTO
	{
		$this->personId = $personId;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPersonName(): string
	{
		return $this->personName;
	}

	/**
	 * @param string $personName
	 * @return TransferDTO
	 */
	public function setPersonName(string $personName): TransferDTO
	{
		$this->personName = $personName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTeamToId(): string
	{
		return $this->teamToId;
	}

	/**
	 * @param string $teamToId
	 * @return TransferDTO
	 */
	public function setTeamToId(string $teamToId): TransferDTO
	{
		$this->teamToId = $teamToId;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTeamToName(): string
	{
		return $this->teamToName;
	}

	/**
	 * @param string $teamToName
	 * @return TransferDTO
	 */
	public function setTeamToName(string $teamToName): TransferDTO
	{
		$this->teamToName = $teamToName;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getTeamFromId(): ?string
	{
		return $this->teamFromId;
	}

	/**
	 * @param string|null $teamFromId
	 * @return TransferDTO
	 */
	public function setTeamFromId(?string $teamFromId): TransferDTO
	{
		$this->teamFromId = $teamFromId;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getTeamFromName(): ?string
	{
		return $this->teamFromName;
	}

	/**
	 * @param string|null $teamFromName
	 * @return TransferDTO
	 */
	public function setTeamFromName(?string $teamFromName): TransferDTO
	{
		$this->teamFromName = $teamFromName;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getMarketValue(): ?string
	{
		return $this->marketValue;
	}

	/**
	 * @param string|null $marketValue
	 * @return TransferDTO
	 */
	public function setMarketValue(?string $marketValue): TransferDTO
	{
		$this->marketValue = $marketValue;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getStartDate(): ?string
	{
		return $this->startDate;
	}

	/**
	 * @param string|null $startDate
	 * @return TransferDTO
	 */
	public function setStartDate(?string $startDate): TransferDTO
	{
		$this->startDate = $startDate;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getEndDate(): ?string
	{
		return $this->endDate;
	}

	/**
	 * @param string|null $endDate
	 * @return TransferDTO
	 */
	public function setEndDate(?string $endDate): TransferDTO
	{
		$this->endDate = $endDate;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getAnnouncedDate(): ?string
	{
		return $this->announcedDate;
	}

	/**
	 * @param string|null $announcedDate
	 * @return TransferDTO
	 */
	public function setAnnouncedDate(?string $announcedDate): TransferDTO
	{
		$this->announcedDate = $announcedDate;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getContractDate(): ?string
	{
		return $this->contractDate;
	}

	/**
	 * @param string|null $contractDate
	 * @return TransferDTO
	 */
	public function setContractDate(?string $contractDate): TransferDTO
	{
		$this->contractDate = $contractDate;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getLike(): int
	{
		return $this->like;
	}

	/**
	 * @param int $like
	 * @return TransferDTO
	 */
	public function setLike(int $like): TransferDTO
	{
		$this->like = $like;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getDislike(): int
	{
		return $this->dislike;
	}

	/**
	 * @param int $dislike
	 * @return TransferDTO
	 */
	public function setDislike(int $dislike): TransferDTO
	{
		$this->dislike = $dislike;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getSeason(): ?string
	{
		return $this->season;
	}

	/**
	 * @param string|null $season
	 * @return TransferDTO
	 */
	public function setSeason(?string $season): TransferDTO
	{
		$this->season = $season;
		return $this;
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
	 * @return TransferDTO
	 */
	public function setType(string $type): TransferDTO
	{
		$this->type = $type;
		return $this;
	}
}