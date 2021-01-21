<?php


namespace Tests\Unit\TeamsMatch;


use App\Http\Services\TeamsMatch\TeamsMatchService;
use App\Models\ReadModels\Embedded\TeamName;
use App\Models\ReadModels\TeamsMatch;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TeamsMatchRepository;
use Faker\Factory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TestCase;
use Tests\Traits\TeamRepositoryTestTrait;
use Tests\Traits\TeamsMatchRepositoryTestTrait;


/**
 * Class TeamsMatchServiceTest
 * @package Tests\Unit\TeamsMatch
 */
class TeamsMatchServiceTest extends TestCase
{
	use TeamRepositoryTestTrait, TeamsMatchRepositoryTestTrait;

	private \Faker\Generator $faker;
	private TeamsMatchRepository $teamsMatchRepository;
	private TeamRepository $teamRepository;
	private TeamsMatchService $teamsMatchService;

	protected function setUp(): void
	{
		$this->createApplication();
		$this->faker = Factory::create();
		$this->teamsMatchRepository = app(TeamsMatchRepository::class);
		$this->teamRepository = app(TeamRepository::class);
		$this->teamsMatchService = app(TeamsMatchService::class);
		$this->createTeamTable();
		$this->createTeamsMatchTable();
	}

	public function testGetTeamsMatchInfo()
	{
		$teamId = $this->faker->uuid;
		$teamName = $this->faker->name;
		/** Create team. */
		$fakeTeamModel = $this->createTeamModel();
		$fakeTeamModel->setId($teamId)->setName(
			(new TeamName())
				->setOriginal($teamName)
				->setOfficial($teamName)
				->setShort($teamName)
		);
		$this->teamRepository->persist($fakeTeamModel);
		/**
		 * Upcoming status.
		 */
		$upcomingItems = [
			[
				'teamId' => $this->faker->uuid,
				'teamName' => $this->faker->name,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2020-12-25Z11:30:00Z'
			],
			[
				'teamId' => $teamId,
				'teamName' => $teamName,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2021-01-19Z11:30:00Z'
			],
			[
				'teamId' => $teamId,
				'teamName' => $teamName,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2021-01-22Z11:30:00Z'
			],
			[
				'teamId' => $teamId,
				'teamName' => $teamName,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2021-01-18Z11:30:00Z'
			],
			[
				'teamId' => $this->faker->uuid,
				'teamName' => $this->faker->name,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2021-01-15Z11:30:00Z'
			],
		];
		foreach ($upcomingItems as $item) {
			$this->createTeamsMatchModel(
				$item['teamId'],
				$item['opponentId'],
				$item['teamName'],
				$item['opponentName'],
				$item['matchId'],
				true,
				TeamsMatch::STATUS_UPCOMING,
				$item['date']
			);
			$this->createTeamsMatchModel(
				$item['opponentId'],
				$item['teamId'],
				$item['opponentName'],
				$item['teamName'],
				$item['matchId'],
				false,
				TeamsMatch::STATUS_UPCOMING,
				$item['date']
			);
		}
		/**
		 * Finished status
		 */
		$finishedItems = [
			[
				'teamId' => $this->faker->uuid,
				'teamName' => $this->faker->name,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2020-12-06Z11:30:00Z'
			],
			[
				'teamId' => $teamId,
				'teamName' => $teamName,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2021-01-12Z11:30:00Z'
			],
			[
				'teamId' => $teamId,
				'teamName' => $teamName,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2021-01-11Z11:30:00Z'
			],
			[
				'teamId' => $teamId,
				'teamName' => $teamName,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2020-10-18Z11:30:00Z'
			],
			[
				'teamId' => $teamId,
				'teamName' => $teamName,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2020-11-11Z11:30:00Z'
			],
			[
				'teamId' => $teamId,
				'teamName' => $teamName,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2021-01-19Z11:30:00Z'
			],
			[
				'teamId' => $teamId,
				'teamName' => $teamName,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2021-01-14Z11:30:00Z'
			],
			[
				'teamId' => $this->faker->uuid,
				'teamName' => $this->faker->name,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2020-01-03Z11:30:00Z'
			],
		];
		foreach ($finishedItems as $item) {
			$this->createTeamsMatchModel(
				$item['teamId'],
				$item['opponentId'],
				$item['teamName'],
				$item['opponentName'],
				$item['matchId'],
				true,
				TeamsMatch::STATUS_FINISHED,
				$item['date']
			);
			$this->createTeamsMatchModel(
				$item['opponentId'],
				$item['teamId'],
				$item['opponentName'],
				$item['teamName'],
				$item['matchId'],
				false,
				TeamsMatch::STATUS_FINISHED,
				$item['date']
			);
		}
		$response = $this->teamsMatchService->getTeamsMatchInfo($teamId);
		$this->assertNotEmpty($response['team']);
		$this->assertNotNull($response['team']['id']);
		$this->assertNotEmpty($response['team']['name']);
		$this->assertNotNull($response['team']['name']['original']);
		$this->assertNotNull($response['team']['name']['short']);
		$this->assertNotNull($response['team']['name']['official']);
		$this->assertInstanceOf(TeamsMatch::class, $response[TeamsMatch::STATUS_UPCOMING][0]);
		foreach ($response[TeamsMatch::STATUS_FINISHED] as $teamsMatch) {
			$this->assertInstanceOf(TeamsMatch::class, $teamsMatch);
		}
	}

	public function testGetTeamsMatchInfoWithoutUpcoming()
	{
		$teamId = $this->faker->uuid;
		$teamName = $this->faker->name;
		/** Create team. */
		$fakeTeamModel = $this->createTeamModel();
		$fakeTeamModel->setId($teamId)->setName(
			(new TeamName())
				->setOriginal($teamName)
				->setOfficial($teamName)
				->setShort($teamName)
		);
		$this->teamRepository->persist($fakeTeamModel);
		/**
		 * Upcoming status.
		 */
		$upcomingItems = [
			[
				'teamId' => $this->faker->uuid,
				'teamName' => $this->faker->name,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2020-12-25Z11:30:00Z'
			],
			[
				'teamId' => $this->faker->uuid,
				'teamName' => $this->faker->name,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2021-01-15Z11:30:00Z'
			],
		];
		foreach ($upcomingItems as $item) {
			$this->createTeamsMatchModel(
				$item['teamId'],
				$item['opponentId'],
				$item['teamName'],
				$item['opponentName'],
				$item['matchId'],
				true,
				TeamsMatch::STATUS_UPCOMING,
				$item['date']
			);
			$this->createTeamsMatchModel(
				$item['opponentId'],
				$item['teamId'],
				$item['opponentName'],
				$item['teamName'],
				$item['matchId'],
				false,
				TeamsMatch::STATUS_UPCOMING,
				$item['date']
			);
		}
		/**
		 * Finished status
		 */
		$finishedItems = [
			[
				'teamId' => $this->faker->uuid,
				'teamName' => $this->faker->name,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2020-12-06Z11:30:00Z'
			],
			[
				'teamId' => $teamId,
				'teamName' => $teamName,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2021-01-12Z11:30:00Z'
			],
			[
				'teamId' => $teamId,
				'teamName' => $teamName,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2021-01-11Z11:30:00Z'
			],
			[
				'teamId' => $teamId,
				'teamName' => $teamName,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2020-10-18Z11:30:00Z'
			],
			[
				'teamId' => $teamId,
				'teamName' => $teamName,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2020-11-11Z11:30:00Z'
			],
			[
				'teamId' => $teamId,
				'teamName' => $teamName,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2021-01-19Z11:30:00Z'
			],
			[
				'teamId' => $teamId,
				'teamName' => $teamName,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2021-01-14Z11:30:00Z'
			],
			[
				'teamId' => $this->faker->uuid,
				'teamName' => $this->faker->name,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2020-01-03Z11:30:00Z'
			],
		];
		foreach ($finishedItems as $item) {
			$this->createTeamsMatchModel(
				$item['teamId'],
				$item['opponentId'],
				$item['teamName'],
				$item['opponentName'],
				$item['matchId'],
				true,
				TeamsMatch::STATUS_FINISHED,
				$item['date']
			);
			$this->createTeamsMatchModel(
				$item['opponentId'],
				$item['teamId'],
				$item['opponentName'],
				$item['teamName'],
				$item['matchId'],
				false,
				TeamsMatch::STATUS_FINISHED,
				$item['date']
			);
		}
		$response = $this->teamsMatchService->getTeamsMatchInfo($teamId);
		$this->assertNotEmpty($response['team']);
		$this->assertNotNull($response['team']['id']);
		$this->assertNotEmpty($response['team']['name']);
		$this->assertNotNull($response['team']['name']['original']);
		$this->assertNotNull($response['team']['name']['short']);
		$this->assertNotNull($response['team']['name']['official']);
		$this->assertEmpty($response[TeamsMatch::STATUS_UPCOMING]);
		foreach ($response[TeamsMatch::STATUS_FINISHED] as $teamsMatch) {
			$this->assertInstanceOf(TeamsMatch::class, $teamsMatch);
		}
	}

	public function testGetTeamsMatchInfoWithoutFinished()
	{
		$teamId = $this->faker->uuid;
		$teamName = $this->faker->name;
		/** Create team. */
		$fakeTeamModel = $this->createTeamModel();
		$fakeTeamModel->setId($teamId)->setName(
			(new TeamName())
				->setOriginal($teamName)
				->setOfficial($teamName)
				->setShort($teamName)
		);
		$this->teamRepository->persist($fakeTeamModel);
		/**
		 * Upcoming status.
		 */
		$upcomingItems = [
			[
				'teamId' => $this->faker->uuid,
				'teamName' => $this->faker->name,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2020-12-25Z11:30:00Z'
			],
			[
				'teamId' => $teamId,
				'teamName' => $teamName,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2021-01-19Z11:30:00Z'
			],
			[
				'teamId' => $teamId,
				'teamName' => $teamName,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2021-01-22Z11:30:00Z'
			],
			[
				'teamId' => $teamId,
				'teamName' => $teamName,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2021-01-18Z11:30:00Z'
			],
			[
				'teamId' => $this->faker->uuid,
				'teamName' => $this->faker->name,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2021-01-15Z11:30:00Z'
			],
		];
		foreach ($upcomingItems as $item) {
			$this->createTeamsMatchModel(
				$item['teamId'],
				$item['opponentId'],
				$item['teamName'],
				$item['opponentName'],
				$item['matchId'],
				true,
				TeamsMatch::STATUS_UPCOMING,
				$item['date']
			);
			$this->createTeamsMatchModel(
				$item['opponentId'],
				$item['teamId'],
				$item['opponentName'],
				$item['teamName'],
				$item['matchId'],
				false,
				TeamsMatch::STATUS_UPCOMING,
				$item['date']
			);
		}
		/**
		 * Finished status
		 */
		$finishedItems = [
			[
				'teamId' => $this->faker->uuid,
				'teamName' => $this->faker->name,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2020-12-06Z11:30:00Z'
			],
			[
				'teamId' => $this->faker->uuid,
				'teamName' => $this->faker->name,
				'matchId' => $this->faker->uuid,
				'opponentId' => $this->faker->uuid,
				'opponentName' => $this->faker->name,
				'date' => '2020-01-03Z11:30:00Z'
			],
		];
		foreach ($finishedItems as $item) {
			$this->createTeamsMatchModel(
				$item['teamId'],
				$item['opponentId'],
				$item['teamName'],
				$item['opponentName'],
				$item['matchId'],
				true,
				TeamsMatch::STATUS_FINISHED,
				$item['date']
			);
			$this->createTeamsMatchModel(
				$item['opponentId'],
				$item['teamId'],
				$item['opponentName'],
				$item['teamName'],
				$item['matchId'],
				false,
				TeamsMatch::STATUS_FINISHED,
				$item['date']
			);
		}
		$response = $this->teamsMatchService->getTeamsMatchInfo($teamId);
		$this->assertNotEmpty($response['team']);
		$this->assertNotNull($response['team']['id']);
		$this->assertNotEmpty($response['team']['name']);
		$this->assertNotNull($response['team']['name']['original']);
		$this->assertNotNull($response['team']['name']['short']);
		$this->assertNotNull($response['team']['name']['official']);
		$this->assertInstanceOf(TeamsMatch::class, $response[TeamsMatch::STATUS_UPCOMING][0]);
		$this->assertEmpty($response[TeamsMatch::STATUS_FINISHED]);
	}

	public function testGetTeamsMatchInfoWhenTeamNotExist()
	{
		$this->expectException(NotFoundHttpException::class);
		$this->teamsMatchService->getTeamsMatchInfo($this->faker->uuid);
	}
}