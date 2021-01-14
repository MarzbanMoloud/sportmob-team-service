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

	public function persistBatchDataForTrophiesByTeam()
	{
		$fakeTeamId = $this->faker->uuid;
		$fakeTeamName = 'Manchester city';

		/** --------------- Example1: PremierLeague_1 --------------- */
		$fakeCompetitionName = 'PremierLeague_1';
		$fakeCompetitionId = $this->faker->uuid;
		/**
		 * PremierLeague_1. season(2020/2021)
		 */
		$fakeTournamentId = $this->faker->uuid;
		/** Winner is Manchester City */
		$trophyModel = (new Trophy())
			->setCompetitionId($fakeCompetitionId)
			->setCompetitionName($fakeCompetitionName)
			->setTeamId($fakeTeamId)
			->setTeamName($fakeTeamName)
			->setPosition(Trophy::POSITION_WINNER)
			->setTournamentId($fakeTournamentId)
			->setTournamentSeason('2020/2021');
		$trophyModel->prePersist();
		$this->trophyRepository->persist($trophyModel);
		/** RunnerUp */
		$trophyModel = (new Trophy())
			->setCompetitionId($fakeCompetitionId)
			->setCompetitionName($fakeCompetitionName)
			->setTeamId($this->faker->uuid)
			->setTeamName($this->faker->name)
			->setPosition(Trophy::POSITION_RUNNER_UP)
			->setTournamentId($fakeTournamentId)
			->setTournamentSeason('2020/2021');
		$trophyModel->prePersist();
		$this->trophyRepository->persist($trophyModel);
		/**
		 * PremierLeague_1. season(2019/2020)
		 */
		$fakeTournamentId = $this->faker->uuid;
		/** Winner is Manchester City */
		$trophyModel = (new Trophy())
			->setCompetitionId($fakeCompetitionId)
			->setCompetitionName($fakeCompetitionName)
			->setTeamId($fakeTeamId)
			->setTeamName($fakeTeamName)
			->setPosition(Trophy::POSITION_WINNER)
			->setTournamentId($fakeTournamentId)
			->setTournamentSeason('2019/2020');
		$trophyModel->prePersist();
		$this->trophyRepository->persist($trophyModel);
		/** RunnerUp */
		$trophyModel = (new Trophy())
			->setCompetitionId($fakeCompetitionId)
			->setCompetitionName($fakeCompetitionName)
			->setTeamId($this->faker->uuid)
			->setTeamName($this->faker->name)
			->setPosition(Trophy::POSITION_RUNNER_UP)
			->setTournamentId($fakeTournamentId)
			->setTournamentSeason('2019/2020');
		$trophyModel->prePersist();
		$this->trophyRepository->persist($trophyModel);
		/**
		 * PremierLeague_1. season(2018/2019)
		 */
		$fakeTournamentId = $this->faker->uuid;
		/** Winner */
		$trophyModel = (new Trophy())
			->setCompetitionId($fakeCompetitionId)
			->setCompetitionName($fakeCompetitionName)
			->setTeamId($fakeTeamId)
			->setTeamName($fakeTeamName)
			->setPosition(Trophy::POSITION_RUNNER_UP)
			->setTournamentId($fakeTournamentId)
			->setTournamentSeason('2018/2019');
		$trophyModel->prePersist();
		$this->trophyRepository->persist($trophyModel);
		/** RunnerUp is Manchester City */
		$trophyModel = (new Trophy())
			->setCompetitionId($fakeCompetitionId)
			->setCompetitionName($fakeCompetitionName)
			->setTeamId($this->faker->uuid)
			->setTeamName($this->faker->name)
			->setPosition(Trophy::POSITION_WINNER)
			->setTournamentId($fakeTournamentId)
			->setTournamentSeason('2018/2019');
		$trophyModel->prePersist();
		$this->trophyRepository->persist($trophyModel);
		/**
		 * PremierLeague_1. season(2017/2018)
		 */
		$fakeTournamentId = $this->faker->uuid;
		/** Winner */
		$trophyModel = (new Trophy())
			->setCompetitionId($fakeCompetitionId)
			->setCompetitionName($fakeCompetitionName)
			->setTeamId($fakeTeamId)
			->setTeamName($fakeTeamName)
			->setPosition(Trophy::POSITION_RUNNER_UP)
			->setTournamentId($fakeTournamentId)
			->setTournamentSeason('2017/2018');
		$trophyModel->prePersist();
		$this->trophyRepository->persist($trophyModel);
		/** RunnerUp is Manchester City */
		$trophyModel = (new Trophy())
			->setCompetitionId($fakeCompetitionId)
			->setCompetitionName($fakeCompetitionName)
			->setTeamId($this->faker->uuid)
			->setTeamName($this->faker->name)
			->setPosition(Trophy::POSITION_WINNER)
			->setTournamentId($fakeTournamentId)
			->setTournamentSeason('2017/2018');
		$trophyModel->prePersist();
		$this->trophyRepository->persist($trophyModel);

		/** --------------- Example1: PremierLeague_2 --------------- */
		$fakeCompetitionName = 'PremierLeague_2';
		$fakeCompetitionId = $this->faker->uuid;
		/**
		 * PremierLeague_2. season(2020/2021)
		 */
		$fakeTournamentId = $this->faker->uuid;
		/** Winner is Manchester City */
		$trophyModel = (new Trophy())
			->setCompetitionId($fakeCompetitionId)
			->setCompetitionName($fakeCompetitionName)
			->setTeamId($fakeTeamId)
			->setTeamName($fakeTeamName)
			->setPosition(Trophy::POSITION_WINNER)
			->setTournamentId($fakeTournamentId)
			->setTournamentSeason('2020/2021');
		$trophyModel->prePersist();
		$this->trophyRepository->persist($trophyModel);
		/** RunnerUp */
		$trophyModel = (new Trophy())
			->setCompetitionId($fakeCompetitionId)
			->setCompetitionName($fakeCompetitionName)
			->setTeamId($this->faker->uuid)
			->setTeamName($this->faker->name)
			->setPosition(Trophy::POSITION_RUNNER_UP)
			->setTournamentId($fakeTournamentId)
			->setTournamentSeason('2020/2021');
		$trophyModel->prePersist();
		$this->trophyRepository->persist($trophyModel);
		/**
		 * PremierLeague_2. season(2019/2020)
		 */
		$fakeTournamentId = $this->faker->uuid;
		/** Winner is Manchester City */
		$trophyModel = (new Trophy())
			->setCompetitionId($fakeCompetitionId)
			->setCompetitionName($fakeCompetitionName)
			->setTeamId($fakeTeamId)
			->setTeamName($fakeTeamName)
			->setPosition(Trophy::POSITION_WINNER)
			->setTournamentId($fakeTournamentId)
			->setTournamentSeason('2019/2020');
		$trophyModel->prePersist();
		$this->trophyRepository->persist($trophyModel);
		/** RunnerUp */
		$trophyModel = (new Trophy())
			->setCompetitionId($fakeCompetitionId)
			->setCompetitionName($fakeCompetitionName)
			->setTeamId($this->faker->uuid)
			->setTeamName($this->faker->name)
			->setPosition(Trophy::POSITION_RUNNER_UP)
			->setTournamentId($fakeTournamentId)
			->setTournamentSeason('2019/2020');
		$trophyModel->prePersist();
		$this->trophyRepository->persist($trophyModel);
		/**
		 * PremierLeague_2. season(2018/2019)
		 */
		$fakeTournamentId = $this->faker->uuid;
		/** Winner */
		$trophyModel = (new Trophy())
			->setCompetitionId($fakeCompetitionId)
			->setCompetitionName($fakeCompetitionName)
			->setTeamId($fakeTeamId)
			->setTeamName($fakeTeamName)
			->setPosition(Trophy::POSITION_RUNNER_UP)
			->setTournamentId($fakeTournamentId)
			->setTournamentSeason('2018/2019');
		$trophyModel->prePersist();
		$this->trophyRepository->persist($trophyModel);
		/** RunnerUp is Manchester City */
		$trophyModel = (new Trophy())
			->setCompetitionId($fakeCompetitionId)
			->setCompetitionName($fakeCompetitionName)
			->setTeamId($this->faker->uuid)
			->setTeamName($this->faker->name)
			->setPosition(Trophy::POSITION_WINNER)
			->setTournamentId($fakeTournamentId)
			->setTournamentSeason('2018/2019');
		$trophyModel->prePersist();
		$this->trophyRepository->persist($trophyModel);
		/**
		 * PremierLeague_2. season(2017/2018)
		 */
		$fakeTournamentId = $this->faker->uuid;
		/** Winner */
		$trophyModel = (new Trophy())
			->setCompetitionId($fakeCompetitionId)
			->setCompetitionName($fakeCompetitionName)
			->setTeamId($fakeTeamId)
			->setTeamName($fakeTeamName)
			->setPosition(Trophy::POSITION_RUNNER_UP)
			->setTournamentId($fakeTournamentId)
			->setTournamentSeason('2017/2018');
		$trophyModel->prePersist();
		$this->trophyRepository->persist($trophyModel);
		/** RunnerUp is Manchester City */
		$trophyModel = (new Trophy())
			->setCompetitionId($fakeCompetitionId)
			->setCompetitionName($fakeCompetitionName)
			->setTeamId($this->faker->uuid)
			->setTeamName($this->faker->name)
			->setPosition(Trophy::POSITION_WINNER)
			->setTournamentId($fakeTournamentId)
			->setTournamentSeason('2017/2018');
		$trophyModel->prePersist();
		$this->trophyRepository->persist($trophyModel);

		return [$fakeTeamId, $fakeCompetitionId];
	}
}