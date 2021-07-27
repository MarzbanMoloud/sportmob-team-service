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

	const DEFAULT_VALUE = '0';

	const ATTR_TEAM_ID = 'teamId';
	const ATTR_ON_LOAN_FROM_ID = 'onLoanFromId';

	const TRANSFER_TYPE_TRANSFERRED = 'transferred';
	const TRANSFER_TYPE_LOAN = 'loan';
	const TRANSFER_TYPE_LOAN_BACK = 'loan_back';

	private string $id;
	private string $personId;
	private ?string $personName = null;
	private string $personType;
	private string $teamId;
	private ?string $teamName = null;
	private string $onLoanFromId = self::DEFAULT_VALUE;
	private ?string $onLoanFromName = null;
	private DateTimeImmutable $dateFrom;
	private ?DateTimeImmutable $dateTo = null;
	private string $season = self::DEFAULT_VALUE;
	private ?int $like = 0;
	private ?int $dislike = 0;
	private ?string $marketValue = null;
	private ?DateTimeImmutable $announcedDate = null;
	private ?DateTimeImmutable $contractDate = null;

	/**
	 * Transfer constructor.
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
	public function getPersonId(): string
	{
		return $this->personId;
	}

	/**
	 * @param string $personId
	 * @return Transfer
	 */
	public function setPersonId(string $personId): Transfer
	{
		$this->personId = $personId;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getPersonName(): ?string
	{
		return $this->personName;
	}

	/**
	 * @param string|null $personName
	 * @return Transfer
	 */
	public function setPersonName(?string $personName): Transfer
	{
		$this->personName = $personName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPersonType(): string
	{
		return $this->personType;
	}

	/**
	 * @param string $personType
	 * @return Transfer
	 */
	public function setPersonType(string $personType): Transfer
	{
		$this->personType = $personType;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTeamId(): string
	{
		return $this->teamId;
	}

	/**
	 * @param string $teamId
	 * @return Transfer
	 */
	public function setTeamId(string $teamId): Transfer
	{
		$this->teamId = $teamId;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getTeamName(): ?string
	{
		return $this->teamName;
	}

	/**
	 * @param string|null $teamName
	 * @return Transfer
	 */
	public function setTeamName(?string $teamName): Transfer
	{
		$this->teamName = $teamName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getOnLoanFromId(): string
	{
		return $this->onLoanFromId;
	}

	/**
	 * @param string $onLoanFromId
	 * @return Transfer
	 */
	public function setOnLoanFromId(string $onLoanFromId): Transfer
	{
		$this->onLoanFromId = $onLoanFromId;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getOnLoanFromName(): ?string
	{
		return $this->onLoanFromName;
	}

	/**
	 * @param string|null $onLoanFromName
	 * @return Transfer
	 */
	public function setOnLoanFromName(?string $onLoanFromName): Transfer
	{
		$this->onLoanFromName = $onLoanFromName;
		return $this;
	}

	/**
	 * @return DateTimeImmutable
	 */
	public function getDateFrom(): DateTimeImmutable
	{
		return $this->dateFrom;
	}

	/**
	 * @param DateTimeImmutable $dateFrom
	 * @return Transfer
	 */
	public function setDateFrom(DateTimeImmutable $dateFrom): Transfer
	{
		$this->dateFrom = $dateFrom;
		return $this;
	}

	/**
	 * @return DateTimeImmutable|null
	 */
	public function getDateTo(): ?DateTimeImmutable
	{
		return $this->dateTo;
	}

	/**
	 * @param DateTimeImmutable|null $dateTo
	 * @return Transfer
	 */
	public function setDateTo(?DateTimeImmutable $dateTo): Transfer
	{
		$this->dateTo = $dateTo;
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

	public function prePersist(): void
	{
		if ($this->dateFrom->getTimestamp() == self::getDateTimeImmutable()->getTimestamp()) {
			return;
		}
		$year = (int)$this->dateFrom->format('Y');
		$month = (int)$this->dateFrom->format('m');
		$vars =
			in_array($month,
				[
					1,
					2
				]) ?
				[
					$year - 1,
					$year
				] :
				[
					$year,
					$year + 1
				];
		$this->season = sprintf("%d-%d", ...$vars);
	}

	/**
	 * @return DateTimeImmutable
	 */
	public static function getDateTimeImmutable(): DateTimeImmutable
	{
		return (new DateTimeImmutable())->setDate(0, 0, 0)->setTime(0, 0, 0);
	}
}