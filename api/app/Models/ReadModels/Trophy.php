<?php


namespace App\Models\ReadModels;


use App\Models\ReadModels\Traits\ReadModelTimestampTrait;
use App\Models\Repositories\DynamoDB\Interfaces\DynamoDBRepositoryModelInterface;
use DateTimeImmutable;


/**
 * Class Trophy
 * @package App\Models\ReadModels
 */
class Trophy implements DynamoDBRepositoryModelInterface
{
	use ReadModelTimestampTrait;

	const POSITION_WINNER = 'winner';
	const POSITION_RUNNER_UP = 'runnerUp';

	private string $teamId;
	private string $teamName;
	private string $position;
	private string $belongTo;
	private string $competitionId;
	private ?string $competitionName = null;
	private string $tournamentId;
	private ?string $tournamentSeason = '0';

	/**
	 * Trophy constructor.
	 */
	public function __construct()
	{
		$this->createdAt = new DateTimeImmutable();
	}

	/**
	 * @param string $teamId
	 * @return $this
	 */
	public function setTeamId(string $teamId): Trophy
	{
		$this->teamId = $teamId;
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
	 * @return string
	 */
	public function getTeamName(): string
	{
		return $this->teamName;
	}

	/**
	 * @param string $teamName
	 * @return Trophy
	 */
	public function setTeamName(string $teamName): Trophy
	{
		$this->teamName = $teamName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPosition(): string
	{
		return $this->position;
	}

	/**
	 * @param string $position
	 * @return Trophy
	 */
	public function setPosition(string $position): Trophy
	{
		$this->position = $position;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getBelongTo(): string
	{
		return $this->belongTo;
	}

	/**
	 * @param string $belongTo
	 * @return Trophy
	 */
	public function setBelongTo(string $belongTo): Trophy
	{
		$this->belongTo = $belongTo;
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
	 * @return Trophy
	 */
	public function setCompetitionId(string $competitionId): Trophy
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
	 * @return Trophy
	 */
	public function setCompetitionName(?string $competitionName): Trophy
	{
		$this->competitionName = $competitionName;
		return $this;
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
	 * @return Trophy
	 */
	public function setTournamentId(string $tournamentId): Trophy
	{
		$this->tournamentId = $tournamentId;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getTournamentSeason(): ?string
	{
		return $this->tournamentSeason;
	}

	/**
	 * @param string|null $tournamentSeason
	 * @return Trophy
	 */
	public function setTournamentSeason(?string $tournamentSeason): Trophy
	{
		$this->tournamentSeason = $tournamentSeason;
		return $this;
	}

	private function createBelongTo()
	{
		$this->belongTo = sprintf("%s#%s", $this->tournamentId, $this->teamId);
	}

	/**
	 * @return mixed
	 */
	public function prePersist()
	{
		$this->createBelongTo();
	}
}