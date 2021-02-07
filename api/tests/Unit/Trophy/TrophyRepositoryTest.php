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

	public function testFindExcludesByCompetitionTournament()
	{
		$fakeTournamentId = '';
		$fakeCompetitionId = '';
		$fakeTeamId = '';
		$opponentTeamId = '';
		$opponentTeamName = '';
		for ($i=0; $i < 10; $i++){
			/**
			 * Item1 - Home
			 */
			$fakeTrophyModel_first = $this->createTrophyModel();
			$fakeTrophyModel_first->prePersist();
			$this->trophyRepository->persist($fakeTrophyModel_first);
			/**
			 * Item1 - Away
			 */
			$fakeTrophyModel_second = $this->createTrophyModel()
				->setTournamentId($fakeTrophyModel_first->getTournamentId())
				->setCompetitionId($fakeTrophyModel_first->getCompetitionId());
			$fakeTrophyModel_second->prePersist();
			$this->trophyRepository->persist($fakeTrophyModel_second);

			if ($i == 5) {
				$fakeTournamentId = $fakeTrophyModel_first->getTournamentId();
				$fakeCompetitionId = $fakeTrophyModel_first->getCompetitionId();
				$fakeTeamId = $fakeTrophyModel_first->getTeamId();
				$opponentTeamId = $fakeTrophyModel_second->getTeamId();
				$opponentTeamName = $fakeTrophyModel_second->getTeamName();
			}
		}
		$trophies = $this->trophyRepository->findExcludesByCompetitionTournament(
			$fakeCompetitionId,
			$fakeTournamentId,
			$fakeTeamId
		);
		$this->assertInstanceOf(Trophy::class, $trophies[0]);
		$this->assertEquals($trophies[0]->getTeamId(), $opponentTeamId);
		$this->assertEquals($trophies[0]->getTeamName(), $opponentTeamName);
	}

	public function testFindExcludesByCompetitionTournamentWhenItemNotExist()
	{
		$trophies = $this->trophyRepository->findExcludesByCompetitionTournament($this->faker->uuid, $this->faker->uuid, $this->faker->uuid);
		$this->assertEmpty($trophies);
	}

	public function testFindByCompetition()
	{
		$fakeCompetitionId = $this->faker->uuid;
		$fakeTournamentId_one = $this->faker->uuid;
		$fakeTournamentId_two = $this->faker->uuid;
		$fakeTeamId = $this->faker->uuid;
		/** Same competition */
			/** Same tournament - 1 */
			$fakeTrophyModel_one = $this->createTrophyModel()
				->setCompetitionId($fakeCompetitionId)
				->setTournamentId($fakeTournamentId_one)
				->setTeamId($fakeTeamId);
			$fakeTrophyModel_one->prePersist();
			$this->trophyRepository->persist($fakeTrophyModel_one);

			$fakeTrophyModel_two = $this->createTrophyModel()
				->setCompetitionId($fakeCompetitionId)
				->setTournamentId($fakeTournamentId_one)
				->setTeamId($this->faker->uuid);
			$fakeTrophyModel_two->prePersist();
			$this->trophyRepository->persist($fakeTrophyModel_two);

			/** Same tournament - 2 */
			$fakeTrophyModel_one = $this->createTrophyModel()
				->setCompetitionId($fakeCompetitionId)
				->setTournamentId($fakeTournamentId_two)
				->setTeamId($fakeTeamId);
			$fakeTrophyModel_one->prePersist();
			$this->trophyRepository->persist($fakeTrophyModel_one);

			$fakeTrophyModel_two = $this->createTrophyModel()
				->setCompetitionId($fakeCompetitionId)
				->setTournamentId($fakeTournamentId_two)
				->setTeamId($this->faker->uuid);
			$fakeTrophyModel_two->prePersist();
			$this->trophyRepository->persist($fakeTrophyModel_two);

		$trophies = $this->trophyRepository->findByCompetition($fakeCompetitionId);
		$this->assertCount(4, $trophies);
		foreach ($trophies as $trophy) {
			$this->assertInstanceOf(Trophy::class, $trophy);
		}
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