<?php


namespace App\Models\ReadModels;


use App\Exceptions\ReadModelValidatorException;
use App\Models\ReadModels\Traits\ReadModelTimestampTrait;
use App\Models\Repositories\DynamoDB\Interfaces\DynamoDBRepositoryModelInterface;
use DateTimeImmutable;


/**
 * Class Transfer
 * @package App\Models\ReadModels
 */
class Transfer implements DynamoDBRepositoryModelInterface
{
	private string $playerId;
	private ?string $playerName = null;
	private ?string $playerPosition = null;
	private ?string $fromTeamId = null;
	private ?string $fromTeamName = null;
	private string $toTeamId;
	private string $toTeamName;
	private ?string $marketValue = null;
	private DateTimeImmutable $startDate;
	private ?DateTimeImmutable $endDate = null;
	private ?DateTimeImmutable $announcedDate = null;
	private ?DateTimeImmutable $contractDate = null;
	private string $type;
	private bool $active = true;
	private ?int $like = 0;
	private ?int $dislike = 0;
	private string $season;

	/**
	 * @return string
	 */
	public function getPlayerId(): string
	{
		return $this->playerId;
	}

	/**
	 * @param string $playerId
	 * @return Transfer
	 */
	public function setPlayerId(string $playerId): Transfer
	{
		$this->playerId = $playerId;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getPlayerName(): ?string
	{
		return $this->playerName;
	}

	/**
	 * @param string|null $playerName
	 * @return Transfer
	 */
	public function setPlayerName(?string $playerName): Transfer
	{
		$this->playerName = $playerName;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getPlayerPosition(): ?string
	{
		return $this->playerPosition;
	}

	/**
	 * @param string|null $playerPosition
	 * @return Transfer
	 */
	public function setPlayerPosition(?string $playerPosition): Transfer
	{
		$this->playerPosition = $playerPosition;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getFromTeamId(): ?string
	{
		return $this->fromTeamId;
	}

	/**
	 * @param string|null $fromTeamId
	 * @return Transfer
	 */
	public function setFromTeamId(?string $fromTeamId): Transfer
	{
		$this->fromTeamId = $fromTeamId;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getFromTeamName(): ?string
	{
		return $this->fromTeamName;
	}

	/**
	 * @param string|null $fromTeamName
	 * @return Transfer
	 */
	public function setFromTeamName(?string $fromTeamName): Transfer
	{
		$this->fromTeamName = $fromTeamName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getToTeamId(): string
	{
		return $this->toTeamId;
	}

	/**
	 * @param string $toTeamId
	 * @return Transfer
	 */
	public function setToTeamId(string $toTeamId): Transfer
	{
		$this->toTeamId = $toTeamId;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getToTeamName(): string
	{
		return $this->toTeamName;
	}

	/**
	 * @param string $toTeamName
	 * @return Transfer
	 */
	public function setToTeamName(string $toTeamName): Transfer
	{
		$this->toTeamName = $toTeamName;
		return $this;
	}

	/**
	 * @return DateTimeImmutable|null
	 */
	public function getEndDate(): ?DateTimeImmutable
	{
		return $this->endDate;
	}

	/**
	 * @param DateTimeImmutable|null $endDate
	 * @return $this
	 */
	public function setEndDate(?DateTimeImmutable $endDate): Transfer
	{
		$this->endDate = $endDate;
		return $this;
	}

	/**
	 * @return DateTimeImmutable|null
	 */
	public function getAnnouncedDate(): ?DateTimeImmutable
	{
		return $this->announcedDate;
	}

	/**
	 * @param DateTimeImmutable|null $announcedDate
	 * @return Transfer
	 */
	public function setAnnouncedDate(?DateTimeImmutable $announcedDate): Transfer
	{
		$this->announcedDate = $announcedDate;
		return $this;
	}

	/**
	 * @return DateTimeImmutable|null
	 */
	public function getContractDate(): ?DateTimeImmutable
	{
		return $this->contractDate;
	}

	/**
	 * @param DateTimeImmutable|null $contractDate
	 * @return Transfer
	 */
	public function setContractDate(?DateTimeImmutable $contractDate): Transfer
	{
		$this->contractDate = $contractDate;
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
	 * @return Transfer
	 */
	public function setType(string $type): Transfer
	{
		$this->type = $type;
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
	 * @return Transfer
	 */
	public function setActive(bool $active): Transfer
	{
		$this->active = $active;
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
	 * @return Transfer
	 */
	public function setLike(?int $like): Transfer
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
	 * @return Transfer
	 */
	public function setDislike(?int $dislike): Transfer
	{
		$this->dislike = $dislike;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSeason(): string
	{
		return $this->season;
	}

	/**
	 * @param string $season
	 * @return Transfer
	 */
	public function setSeason(string $season): Transfer
	{
		$this->season = $season;
		return $this;
	}

	/**
	 * @return DateTimeImmutable
	 */
	public function getStartDate(): DateTimeImmutable
	{
		return $this->startDate;
	}

	/**
	 * @param DateTimeImmutable $startDate
	 * @return Transfer
	 */
	public function setStartDate(DateTimeImmutable $startDate): Transfer
	{
		$this->startDate = $startDate;
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
	 * @return Transfer
	 */
	public function setMarketValue(?string $marketValue): Transfer
	{
		$this->marketValue = $marketValue;
		return $this;
	}

	/**
	 * @throws ReadModelValidatorException
	 */
	public function prePersist()
	{
		if (!$this->fromTeamId && !$this->toTeamId || $this->fromTeamId === $this->toTeamId) {
			throw new ReadModelValidatorException( 'fromTeamId and toTeamId could not be null at same time.' );
		}
		$year         = (int)$this->startDate->format( 'Y' );
		$month        = (int)$this->startDate->format( 'm' );
		$vars         =
			in_array( $month,
				[
					1,
					2
				] ) ?
				[
					$year - 1,
					$year
				] :
				[
					$year,
					$year + 1
				];
		$this->season = sprintf( "%d-%d", ...$vars );
	}
}