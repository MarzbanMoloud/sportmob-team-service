<?php


namespace App\Models\ReadModels;


use App\Models\ReadModels\Traits\ReadModelTimestampTrait;
use App\ValueObjects\ReadModel\TeamName;
use App\Models\Repositories\DynamoDB\Interfaces\DynamoDBRepositoryModelInterface;
use DateTime;


/**
 * Class TeamsMatch
 * @package App\Models\ReadModels
 */
class TeamsMatch implements DynamoDBRepositoryModelInterface
{
	use ReadModelTimestampTrait;

	const EVALUATION_DRAW = 'draw';
	const EVALUATION_LOSS = 'loss';
	const EVALUATION_WIN = 'win';
	const STATUS_FINISHED = 'finished';
	const STATUS_UPCOMING = 'upcoming';
	const STATUS_UNKNOWN = 'unknown';
	const COVERAGE_LOW = 'low';

	private string $competitionId;
	private string $tournamentId;
	private string $stageId;
	private ?string $stageName = null;
	private ?string $competitionName = null;
	private string $matchId;
	private string $teamId;
	private TeamName $teamName;
	private string $opponentId;
	private TeamName $opponentName;
	private bool $isHome;
	private ?string $coverage = null;
	private ?string $evaluation = null;
	private string $sortKey;
	private string $status;
	private array $result = [];


	public function __construct()
	{
		$this->setUpdatedAt(new \DateTime());
	}

	/**
	 * @return string
	 */
	public function getTournamentId(): string
	{
		return $this->tournamentId;
	}

	/**
	 * @param string $tournamentId
	 * @return TeamsMatch
	 */
	public function setTournamentId(string $tournamentId): TeamsMatch
	{
		$this->tournamentId = $tournamentId;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getStageId(): string
	{
		return $this->stageId;
	}

	/**
	 * @param string $stageId
	 * @return TeamsMatch
	 */
	public function setStageId(string $stageId): TeamsMatch
	{
		$this->stageId = $stageId;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getStageName(): ?string
	{
		return $this->stageName;
	}

	/**
	 * @param string|null $stageName
	 * @return TeamsMatch
	 */
	public function setStageName(?string $stageName): TeamsMatch
	{
		$this->stageName = $stageName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMatchId(): string
	{
		return $this->matchId;
	}

	/**
	 * @param string $matchId
	 * @return TeamsMatch
	 */
	public function setMatchId(string $matchId): TeamsMatch
	{
		$this->matchId = $matchId;
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
	 * @return TeamsMatch
	 */
	public function setTeamId(string $teamId): TeamsMatch
	{
		$this->teamId = $teamId;
		return $this;
	}

	/**
	 * @return TeamName
	 */
	public function getTeamName(): TeamName
	{
		return $this->teamName;
	}

	/**
	 * @param TeamName $teamName
	 * @return TeamsMatch
	 */
	public function setTeamName(TeamName $teamName): TeamsMatch
	{
		$this->teamName = $teamName;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isHome(): bool
	{
		return $this->isHome;
	}

	/**
	 * @param bool $isHome
	 * @return TeamsMatch
	 */
	public function setIsHome(bool $isHome): TeamsMatch
	{
		$this->isHome = $isHome;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getOpponentId(): string
	{
		return $this->opponentId;
	}

	/**
	 * @param string $opponentId
	 * @return TeamsMatch
	 */
	public function setOpponentId(string $opponentId): TeamsMatch
	{
		$this->opponentId = $opponentId;
		return $this;
	}

	/**
	 * @return TeamName
	 */
	public function getOpponentName(): TeamName
	{
		return $this->opponentName;
	}

	/**
	 * @param TeamName $opponentName
	 * @return TeamsMatch
	 */
	public function setOpponentName(TeamName $opponentName): TeamsMatch
	{
		$this->opponentName = $opponentName;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getEvaluation(): ?string
	{
		return $this->evaluation;
	}

	/**
	 * @param string|null $evaluation
	 * @return TeamsMatch
	 */
	public function setEvaluation(?string $evaluation): TeamsMatch
	{
		$this->evaluation = $evaluation;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSortKey(): string
	{
		return $this->sortKey;
	}

	/**
	 * @param string $sortKey
	 * @return TeamsMatch
	 */
	public function setSortKey(string $sortKey): TeamsMatch
	{
		$this->sortKey = $sortKey;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getStatus(): string
	{
		return $this->status;
	}

	/**
	 * @param string $status
	 * @return TeamsMatch
	 */
	public function setStatus(string $status): TeamsMatch
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getResult(): array
	{
		return $this->result;
	}

	/**
	 * @param array $result
	 * @return TeamsMatch
	 */
	public function setResult(array $result): TeamsMatch
	{
		$this->result = $result;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCompetitionId(): string
	{
		return $this->competitionId;
	}

	/**
	 * @param string $competitionId
	 * @return TeamsMatch
	 */
	public function setCompetitionId(string $competitionId): TeamsMatch
	{
		$this->competitionId = $competitionId;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getCompetitionName(): ?string
	{
		return $this->competitionName;
	}

	/**
	 * @param string|null $competitionName
	 * @return TeamsMatch
	 */
	public function setCompetitionName(?string $competitionName): TeamsMatch
	{
		$this->competitionName = $competitionName;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getCoverage(): ?string
	{
		return $this->coverage;
	}

	/**
	 * @param string|null $coverage
	 * @return TeamsMatch
	 */
	public function setCoverage(?string $coverage): TeamsMatch
	{
		$this->coverage = $coverage;
		return $this;
	}

	/**
	 * @param DateTime $matchDate
	 * @param string $status
	 * @return string
	 */
	public static function generateSortKey(DateTime $matchDate, string $status): string
	{
		return sprintf('%s#%s', $status, $matchDate->format('Y-m-d H:i:s'));
	}

	/**
	 * @param string $sortKey
	 * @return DateTime
	 * @throws \Exception
	 */
	public static function getMatchDate(string $sortKey): DateTime
	{
		$matchDate = explode('#', $sortKey)[1];
		return (new DateTime($matchDate));
	}
}