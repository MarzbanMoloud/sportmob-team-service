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
	use ReadModelTimestampTrait;

	const ATTR_TO_TEAM_ID = 'toTeamId';
	const ATTR_FROM_TEAM_ID = 'fromTeamId';

	private string $id;
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
	private int $active = 1;
	private ?int $like = 0;
	private ?int $dislike = 0;
	private string $season = '0';

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
	 * @return Transfer
	 */
	public function setId(string $id): Transfer
	{
		$this->id = $id;
		return $this;
	}

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
	 * @return DateTimeImmutable|null
	 */
	public function getStartDate(): ?DateTimeImmutable
	{
		return $this->startDate;
	}

	/**
	 * @param DateTimeImmutable|null $startDate
	 * @return Transfer
	 */
	public function setStartDate(?DateTimeImmutable $startDate): Transfer
	{
		if (!$startDate){
			$startDate = self::getDateTimeImmutable();
		}
		$this->startDate = $startDate;
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
	 * @return Transfer
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
	 * @return int
	 */
	public function getActive(): int
	{
		return $this->active;
	}

	/**
	 * @param int $active
	 * @return Transfer
	 */
	public function setActive(int $active): Transfer
	{
		$this->active = $active;
		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getLike(): ?int
	{
		return $this->like;
	}

	/**
	 * @param int|null $like
	 * @return Transfer
	 */
	public function setLike(?int $like): Transfer
	{
		$this->like = $like;
		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getDislike(): ?int
	{
		return $this->dislike;
	}

	/**
	 * @param int|null $dislike
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
	 * @throws ReadModelValidatorException
	 */
	public function prePersist()
	{
		if (!$this->fromTeamId && !$this->toTeamId) {
			throw new ReadModelValidatorException( 'fromTeamId and toTeamId could not be null at same time.' );
		}
		if ($this->startDate->getTimestamp() == self::getDateTimeImmutable()->getTimestamp()) {
			return;
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

	/**
	 * @return DateTimeImmutable
	 */
	public static function getDateTimeImmutable(): DateTimeImmutable
	{
		return (new DateTimeImmutable())->setDate(0, 0, 0)->setTime(0, 0, 0);
	}
}