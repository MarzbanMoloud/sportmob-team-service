<?php


namespace Tests\Traits;


use App\Models\ReadModels\Trophy;
use App\Models\Repositories\TrophyRepository;


/**
 * Trait TrophyRepositoryTestTrait
 * @package Tests\Traits
 */
trait TrophyRepositoryTestTrait
{
	public function createTrophyTable(): void
	{
		if (in_array(TrophyRepository::getTableName(), $this->trophyRepository->getDynamoDbClient()->listTables()->toArray()['TableNames'])) {
			$this->trophyRepository->drop();
		}
		$this->trophyRepository->createTable();
	}

	/**
	 * @return Trophy
	 */
	public function createTrophyModel(): Trophy
	{
		$positions = [Trophy::POSITION_RUNNER_UP, Trophy::POSITION_WINNER];
		$trophyModel = (new Trophy())
			->setTeamId($this->faker->uuid)
			->setTeamName($this->faker->name)
			->setPosition($positions[$this->faker->numberBetween(0, 1)])
			->setCompetitionId($this->faker->uuid)
			->setCompetitionName($this->faker->name)
			->setTournamentId($this->faker->uuid)
			->setTournamentSeason('2020/2021');
		$trophyModel->prePersist();
		return $trophyModel;
	}
}