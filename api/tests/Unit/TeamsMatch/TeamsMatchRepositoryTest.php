<?php


namespace Tests\Unit\TeamsMatch;


use App\Models\ReadModels\TeamsMatch;
use App\Models\Repositories\TeamsMatchRepository;
use Faker\Factory;
use TestCase;
use Tests\Traits\TeamsMatchRepositoryTestTrait;


/**
 * Class TeamsMatchRepositoryTest
 * @package Tests\Unit\TeamsMatch
 */
class TeamsMatchRepositoryTest extends TestCase
{
	use TeamsMatchRepositoryTestTrait;

	private \Faker\Generator $faker;
	private TeamsMatchRepository $teamsMatchRepository;

	protected function setUp(): void
	{
		$this->createApplication();
		$this->faker = Factory::create();
		$this->teamsMatchRepository = app(TeamsMatchRepository::class);
		$this->createTeamsMatchTable();
	}

	/**
	 * @throws \Exception
	 */
	public function testFind()
	{
		$fakeMatchId = $this->faker->uuid;
		$teamId = $this->faker->uuid;
		$opponentId = $this->faker->uuid;
		$teamName = $this->faker->name;
		$opponentName = $this->faker->name;

		$this->createTeamsMatchModel(
			$teamId,
			$opponentId,
			$teamName,
			$opponentName,
			$fakeMatchId,
			true
		);
		$this->createTeamsMatchModel(
			$opponentId,
			$teamId,
			$opponentName,
			$teamName,
			$fakeMatchId,
			false
		);

		$homeResponse = $this->teamsMatchRepository->find([
			'matchId' => $fakeMatchId,
			'teamId' => $teamId
		]);
		$awayResponse = $this->teamsMatchRepository->find([
			'matchId' => $fakeMatchId,
			'teamId' => $opponentId
		]);
		$this->assertInstanceOf(TeamsMatch::class, $homeResponse);
		$this->assertInstanceOf(TeamsMatch::class, $awayResponse);
	}

	public function testFindWhenItemNotExist()
	{
		$response = $this->teamsMatchRepository->find([
			'matchId' => $this->faker->uuid,
			'teamId' => $this->faker->uuid
		]);
		$this->assertNull($response);
	}

	/**
	 * @throws \Exception
	 */
	public function testFindTeamsMatchByTeamId()
	{
		$teamId = $this->faker->uuid;
		$opponentId = $this->faker->uuid;
		$teamName = $this->faker->name;
		$opponentName = $this->faker->name;
		/**
		 * Upcoming status.
		 */
		$fakeMatchIdForUpcoming = $this->faker->uuid;

		$this->createTeamsMatchModel(
			$teamId,
			$opponentId,
			$teamName,
			$opponentName,
			$fakeMatchIdForUpcoming,
			true
		);
		$this->createTeamsMatchModel(
			$opponentId,
			$teamId,
			$opponentName,
			$teamName,
			$fakeMatchIdForUpcoming,
			false
		);
		/**
		 * Finished status.
		 */
		$fakeMatchIdForFinished = $this->faker->uuid;

		$this->createTeamsMatchModel(
			$teamId,
			$opponentId,
			$teamName,
			$opponentName,
			$fakeMatchIdForFinished,
			true,
			TeamsMatch::STATUS_FINISHED
		);
		$this->createTeamsMatchModel(
			$opponentId,
			$teamId,
			$opponentName,
			$teamName,
			$fakeMatchIdForFinished,
			false,
			TeamsMatch::STATUS_FINISHED
		);
		$finishedResponse = $this->teamsMatchRepository->findTeamsMatchByTeamId($teamId, TeamsMatch::STATUS_FINISHED);
		$upcomingResponse = $this->teamsMatchRepository->findTeamsMatchByTeamId($teamId, TeamsMatch::STATUS_UPCOMING);
		$unknownResponse = $this->teamsMatchRepository->findTeamsMatchByTeamId($teamId, TeamsMatch::STATUS_UNKNOWN);
		$this->assertInstanceOf(TeamsMatch::class, $finishedResponse[0]);
		$this->assertEquals($fakeMatchIdForFinished, $finishedResponse[0]->getMatchId());
		$this->assertInstanceOf(TeamsMatch::class, $upcomingResponse[0]);
		$this->assertEquals($fakeMatchIdForUpcoming, $upcomingResponse[0]->getMatchId());
		$this->assertEmpty($unknownResponse);
	}

	public function testFindTeamsMatchByTeamIdWhenItemNotExist()
	{
		$response = $this->teamsMatchRepository->findTeamsMatchByTeamId($this->faker->uuid, TeamsMatch::STATUS_FINISHED);
		$this->assertEmpty($response);
	}

	public function testFindTeamsMatchByMatchId()
	{
		$fakeTeamsMatchModel = $this->createTeamsMatchModel(
			$this->faker->uuid,
			$this->faker->uuid,
			$this->faker->name,
			$this->faker->name,
			$this->faker->uuid
		);
		$response = $this->teamsMatchRepository->findTeamsMatchByMatchId($fakeTeamsMatchModel->getMatchId());
		$this->assertInstanceOf(TeamsMatch::class, $response[0]);
	}

	public function testFindTeamsMatchByMatchIdWhenItemNotExist()
	{
		$response = $this->teamsMatchRepository->findTeamsMatchByMatchId($this->faker->uuid);
		$this->assertEmpty($response);
	}

	protected function tearDown(): void
	{
		$this->teamsMatchRepository->drop();
	}
}