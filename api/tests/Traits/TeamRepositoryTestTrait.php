<?php


namespace Tests\Traits;


use App\ValueObjects\ReadModel\TeamName;
use App\Models\ReadModels\Team;
use App\Models\Repositories\TeamRepository;


/**
 * Trait TeamRepositoryTestTrait
 * @package Tests\Traits
 */
trait TeamRepositoryTestTrait
{
	public function createTeamTable(): void
	{
		if (in_array(TeamRepository::getTableName(), $this->teamRepository->getDynamoDbClient()->listTables()->toArray()['TableNames'])) {
			$this->teamRepository->drop();
		}
		$this->teamRepository->createTable();
	}

	/**
	 * @return Team
	 */
	public function createTeamModel(): Team
	{
		$gender = ['female', 'male'];
		$types = ['club', 'national'];
		$active = [true, false];
		return (new Team())
			->setId($this->faker->uuid)
			->setGender($gender[$this->faker->numberBetween(0, 1)])
			->setFounded('12112')
			->setCountry($this->faker->country)
			->setCountryId($this->faker->uuid)
			->setCity($this->faker->city)
			->setType($types[$this->faker->numberBetween(0, 1)])
			->setActive($active[$this->faker->numberBetween(0, 1)])
			->setName(
				(new TeamName())
					->setOriginal($this->faker->name)
					->setOfficial($this->faker->name)
					->setShort($this->faker->name)
			);
	}
}