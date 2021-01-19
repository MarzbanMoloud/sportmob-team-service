<?php


namespace Tests\Unit\Trophy;


use App\Models\ReadModels\Trophy;
use App\Models\Repositories\TrophyRepository;
use Faker\Factory;
use TestCase;
use Tests\Traits\TrophyRepositoryTestTrait;


/**
 * Class TrophyRepositoryTest
 * @package Tests\Unit\Trophy
 */
class TrophyRepositoryTest extends TestCase
{
	use TrophyRepositoryTestTrait;

	private \Faker\Generator $faker;
	private TrophyRepository $trophyRepository;

	protected function setUp(): void
	{
		$this->createApplication();
		$this->faker = Factory::create();
		$this->trophyRepository = app(TrophyRepository::class);
		$this->createTrophyTable();
	}

	public function testFindByTeamId()
	{
		$teamId = $this->faker->uuid;
		$teamName = $this->faker->name;
		for ($i = 0; $i < 3; $i++) {
			$fakeTrophyModel = $this->createTrophyModel()
				->setTeamId($teamId)
				->setTeamName($teamName);
			$this->trophyRepository->persist($fakeTrophyModel);
		}
		$trophies = $this->trophyRepository->findByTeamId($teamId);
		$this->assertCount(3, $trophies);
		$this->assertInstanceOf(Trophy::class, $trophies[0]);
	}

	public function testFindByTeamIdWhenItemNotExist()
	{
		$trophies = $this->trophyRepository->findByTeamId($this->faker->uuid);
		$this->assertEmpty($trophies);
	}

	public function testFindByTournamentId()
	{
		$fakeCompetitionId = $this->faker->uuid;
		$fakeTournamentId = '';
		for ($i = 0; $i < 3; $i++) {
			$fakeTrophyModel = $this->createTrophyModel()
				->setCompetitionId($fakeCompetitionId);
			if ($i == 2) {
				$fakeTournamentId = $fakeTrophyModel->getTournamentId();
			}
			$fakeTrophyModel->prePersist();
			$this->trophyRepository->persist($fakeTrophyModel);
		}
		$trophies = $this->trophyRepository->findByTournamentId($fakeTournamentId);
		$this->assertInstanceOf(Trophy::class, $trophies[0]);
	}

	public function testFindByTournamentIdWhenItemNotExist()
	{
		$trophies = $this->trophyRepository->findByTournamentId($this->faker->uuid);
		$this->assertEmpty($trophies);
	}

	public function testFindExcludesByCompetitionTournament()
	{
		$fakeCompetitionId = $this->faker->uuid;
		$fakeTournamentId = $this->faker->uuid;
		/**
		 * First team.
		 */
		$fakeTrophyModel_first = $this->createTrophyModel()
			->setTournamentId($fakeTournamentId)
			->setCompetitionId($fakeCompetitionId);
		$fakeTrophyModel_first->prePersist();
		$this->trophyRepository->persist($fakeTrophyModel_first);

		/**
		 * Second team.
		 */
		$fakeTrophyModel_second = $this->createTrophyModel()
			->setTournamentId($fakeTournamentId)
			->setCompetitionId($fakeCompetitionId);
		$fakeTrophyModel_second->prePersist();
		$this->trophyRepository->persist($fakeTrophyModel_second);

		$trophies = $this->trophyRepository->findExcludesByCompetitionTournament(
			$fakeCompetitionId,
			$fakeTournamentId,
			$fakeTrophyModel_second->getTeamId()
		);
		$this->assertInstanceOf(Trophy::class, $trophies[0]);
		$this->assertEquals($trophies[0]->getTeamId(), $fakeTrophyModel_first->getTeamId());
		$this->assertEquals($trophies[0]->getTeamName(), $fakeTrophyModel_first->getTeamName());
	}

	public function testFindExcludesByCompetitionTournamentWhenItemNotExist()
	{
		$trophies = $this->trophyRepository->findExcludesByCompetitionTournament($this->faker->uuid, $this->faker->uuid, $this->faker->uuid);
		$this->assertEmpty($trophies);
	}

	public function testFindByCompetition()
	{
		$fakeCompetitionId = $this->faker->uuid;
		$fakeTournamentId = $this->faker->uuid;
		$fakeTeamId = $this->faker->uuid;
		$fakeTrophyModel_one = $this->createTrophyModel()
			->setTournamentId($fakeTournamentId)
			->setCompetitionId($fakeCompetitionId)
			->setTeamId($fakeTeamId);
		$fakeTrophyModel_one->prePersist();
		$this->trophyRepository->persist($fakeTrophyModel_one);

		$fakeTrophyModel_two = $this->createTrophyModel()
			->setTournamentId($fakeTournamentId)
			->setCompetitionId($fakeCompetitionId)
			->setTeamId($this->faker->uuid);
		$fakeTrophyModel_two->prePersist();
		$this->trophyRepository->persist($fakeTrophyModel_two);

		$trophies = $this->trophyRepository->findByCompetition($fakeCompetitionId);
		$this->assertCount(2, $trophies);
		$this->assertInstanceOf(Trophy::class, $trophies[0]);
		$this->assertInstanceOf(Trophy::class, $trophies[1]);
	}

	public function testFindByCompetitionWhenItemNotExist()
	{
		$trophies = $this->trophyRepository->findByCompetition($this->faker->uuid);
		$this->assertEmpty($trophies);
	}

	protected function tearDown(): void
	{
		$this->trophyRepository->drop();
	}
}