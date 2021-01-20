<?php


namespace Tests\Traits;


use App\Models\ReadModels\Embedded\TeamName;
use App\Models\ReadModels\TeamsMatch;
use App\Models\Repositories\TeamsMatchRepository;
use DateTime;


/**
 * Trait TeamsMatchRepositoryTestTrait
 * @package Tests\Traits
 */
trait TeamsMatchRepositoryTestTrait
{
	public function createTeamsMatchTable(): void
	{
		if (in_array(TeamsMatchRepository::getTableName(), $this->teamsMatchRepository->getDynamoDbClient()->listTables()->toArray()['TableNames'])) {
			$this->teamsMatchRepository->drop();
		}
		$this->teamsMatchRepository->createTable();
	}

	/**
	 * @param string $teamId
	 * @param string $opponentId
	 * @param string $teamName
	 * @param string $opponentName
	 * @param string $matchId
	 * @param bool $home
	 * @param string|null $status
	 * @return TeamsMatch
	 */
	public function createTeamsMatchModel(
		string $teamId,
		string $opponentId,
		string $teamName,
		string $opponentName,
		string $matchId,
		bool $home = true,
		?string $status = null
	): TeamsMatch {
		$fakeTeamsMatchModel = (new TeamsMatch())
			->setCompetitionId($this->faker->uuid)
			->setTeamId($teamId)
			->setTeamName(
				(new TeamName())
					->setShort($teamName)
					->setOriginal($teamName)
					->setOfficial($teamName)
			)
			->setOpponentId($opponentId)
			->setOpponentName(
				(new TeamName())
					->setShort($opponentName)
					->setOriginal($opponentName)
					->setOfficial($opponentName)
			)
			->setIsHome($home ? true : false)
			->setMatchId($matchId)
			->setStatus($status ?? TeamsMatch::STATUS_UPCOMING)
			->setSortKey(TeamsMatch::generateSortKey(new DateTime(), $status ?? TeamsMatch::STATUS_UPCOMING));
		$this->teamsMatchRepository->persist($fakeTeamsMatchModel);
		return $fakeTeamsMatchModel;

	}
}