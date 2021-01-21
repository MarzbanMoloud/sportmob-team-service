<?php


namespace Tests\Feature\TeamsMatch;


use App\Models\ReadModels\Embedded\TeamName;
use App\Models\ReadModels\TeamsMatch;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TeamsMatchRepository;
use App\Services\Cache\Interfaces\TeamsMatchCacheServiceInterface;
use Faker\Factory;
use TestCase;
use Tests\Traits\TeamRepositoryTestTrait;
use Tests\Traits\TeamsMatchRepositoryTestTrait;


/**
 * Class TeamControllerTest
 * @package Tests\Feature\TeamsMatch
 */
class TeamControllerTest extends TestCase
{
	use TeamsMatchRepositoryTestTrait, TeamRepositoryTestTrait;

	private TeamsMatchRepository $teamsMatchRepository;
	private \Faker\Generator $faker;
	private TeamsMatchCacheServiceInterface $TeamsMatchCacheService;
	private TeamRepository $teamRepository;

	protected function setUp(): void
	{
		parent::setUp();
		$this->createApplication();
		$this->teamsMatchRepository = app(TeamsMatchRepository::class);
		$this->teamRepository = app(TeamRepository::class);
		$this->TeamsMatchCacheService = app(TeamsMatchCacheServiceInterface::class);
		$this->faker = Factory::create();
		$this->createTeamsMatchTable();
		$this->createTeamTable();
	}

	/**
	 * @throws \Exception
	 */
	public function testOverview()
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
		/**
		 * Read from DB.
		 */
		$this->TeamsMatchCacheService->flush();
		$response = $this->json('GET', sprintf('/en/teams/overview/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['team']);
		$this->assertNotEmpty($response['data']['team']['id']);
		$this->assertNotNull($response['data']['team']['name']['original']);
		$this->assertNotNull($response['data']['team']['name']['official']);
		$this->assertNotNull($response['data']['team']['name']['short']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['competition']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['competition']['id']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['competition']['name']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['id']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']['original']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']['short']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['id']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']['original']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']['short']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['date']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['coverage']);
		$this->assertCount(5, $response['data'][TeamsMatch::STATUS_FINISHED]);
		foreach ($response['data'][TeamsMatch::STATUS_FINISHED] as $item) {
			$this->assertNotEmpty($item['team']);
			$this->assertNotNull($item['team']['id']);
			$this->assertNotEmpty($item['team']['name']);
			$this->assertNotNull($item['team']['name']['original']);
			$this->assertNotNull($item['team']['name']['short']);
			$this->assertNotNull($item['date']);
			$this->assertNotEmpty($item['result']);
			$this->assertNotNull($item['result']['score']['home']);
			$this->assertNotNull($item['result']['score']['away']);
			$this->assertNotNull($item['result']['penalty']['home']);
			$this->assertNotNull($item['result']['penalty']['away']);
		}
		/**
		 * Read from Cache.
		 */
		$this->teamsMatchRepository->drop();
		$response = $this->json('GET', sprintf('/en/teams/overview/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['team']);
		$this->assertNotEmpty($response['data']['team']['id']);
		$this->assertNotNull($response['data']['team']['name']['original']);
		$this->assertNotNull($response['data']['team']['name']['official']);
		$this->assertNotNull($response['data']['team']['name']['short']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['competition']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['competition']['id']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['competition']['name']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['id']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']['original']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']['short']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['id']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']['original']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']['short']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['date']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['coverage']);
		$this->assertCount(5, $response['data'][TeamsMatch::STATUS_FINISHED]);
		foreach ($response['data'][TeamsMatch::STATUS_FINISHED] as $item) {
			$this->assertNotEmpty($item['team']);
			$this->assertNotNull($item['team']['id']);
			$this->assertNotEmpty($item['team']['name']);
			$this->assertNotNull($item['team']['name']['original']);
			$this->assertNotNull($item['team']['name']['short']);
			$this->assertNotNull($item['date']);
			$this->assertNotEmpty($item['result']);
			$this->assertNotNull($item['result']['score']['home']);
			$this->assertNotNull($item['result']['score']['away']);
			$this->assertNotNull($item['result']['score']['home']);
			$this->assertNotNull($item['result']['score']['away']);
		}
	}

	public function testOverviewWithoutUpcoming()
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
		/**
		 * Read from DB
		 */
		$this->TeamsMatchCacheService->flush();
		$response = $this->json('GET', sprintf('/en/teams/overview/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['team']);
		$this->assertNotEmpty($response['data']['team']['id']);
		$this->assertNotNull($response['data']['team']['name']['original']);
		$this->assertNotNull($response['data']['team']['name']['official']);
		$this->assertNotNull($response['data']['team']['name']['short']);
		$this->assertEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]);
		$this->assertCount(5, $response['data'][TeamsMatch::STATUS_FINISHED]);
		foreach ($response['data'][TeamsMatch::STATUS_FINISHED] as $item) {
			$this->assertNotEmpty($item['team']);
			$this->assertNotNull($item['team']['id']);
			$this->assertNotEmpty($item['team']['name']);
			$this->assertNotNull($item['team']['name']['original']);
			$this->assertNotNull($item['team']['name']['short']);
			$this->assertNotNull($item['date']);
			$this->assertNotEmpty($item['result']);
			$this->assertNotNull($item['result']['score']['home']);
			$this->assertNotNull($item['result']['score']['away']);
			$this->assertNotNull($item['result']['score']['home']);
			$this->assertNotNull($item['result']['score']['away']);
		}
		/**
		 * Read from Cache.
		 */
		$this->teamsMatchRepository->drop();
		$response = $this->json('GET', sprintf('/en/teams/overview/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['team']);
		$this->assertNotEmpty($response['data']['team']['id']);
		$this->assertNotNull($response['data']['team']['name']['original']);
		$this->assertNotNull($response['data']['team']['name']['official']);
		$this->assertNotNull($response['data']['team']['name']['short']);
		$this->assertEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]);
		$this->assertCount(5, $response['data'][TeamsMatch::STATUS_FINISHED]);
		foreach ($response['data'][TeamsMatch::STATUS_FINISHED] as $item) {
			$this->assertNotEmpty($item['team']);
			$this->assertNotNull($item['team']['id']);
			$this->assertNotEmpty($item['team']['name']);
			$this->assertNotNull($item['team']['name']['original']);
			$this->assertNotNull($item['team']['name']['short']);
			$this->assertNotNull($item['date']);
			$this->assertNotEmpty($item['result']);
			$this->assertNotNull($item['result']['score']['home']);
			$this->assertNotNull($item['result']['score']['away']);
			$this->assertNotNull($item['result']['score']['home']);
			$this->assertNotNull($item['result']['score']['away']);
		}
	}

	public function testOverviewWithoutFinished()
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
		/**
		 * Read from DB.
		 */
		$this->TeamsMatchCacheService->flush();
		$response = $this->json('GET', sprintf('/en/teams/overview/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['team']);
		$this->assertNotEmpty($response['data']['team']['id']);
		$this->assertNotNull($response['data']['team']['name']['original']);
		$this->assertNotNull($response['data']['team']['name']['official']);
		$this->assertNotNull($response['data']['team']['name']['short']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['competition']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['competition']['name']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['competition']['id']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['id']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']['original']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']['short']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['id']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']['original']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']['short']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['date']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['coverage']);
		$this->assertEmpty($response['data'][TeamsMatch::STATUS_FINISHED]);
		/**
		 * Read from Cache.
		 */
		$this->teamsMatchRepository->drop();
		$response = $this->json('GET', sprintf('/en/teams/overview/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['team']);
		$this->assertNotEmpty($response['data']['team']['id']);
		$this->assertNotNull($response['data']['team']['name']['original']);
		$this->assertNotNull($response['data']['team']['name']['official']);
		$this->assertNotNull($response['data']['team']['name']['short']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['competition']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['competition']['name']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['competition']['id']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['id']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']['original']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']['short']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['id']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']['original']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']['short']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['date']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['coverage']);
		$this->assertEmpty($response['data'][TeamsMatch::STATUS_FINISHED]);
	}

	public function testOverviewWhenTeamNotExist()
	{
		$teamId = $this->faker->uuid;
		$response = $this->json('GET', sprintf('/en/teams/overview/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertNotNull($response['message']);
		$this->assertEquals(config('common.error_codes.resource_not_found'), $response['code']);
	}

	public function testFavorite()
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
		/**
		 * Read from DB.
		 */
		$this->TeamsMatchCacheService->flush();
		$response = $this->json('GET', sprintf('/en/teams/favorite/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['team']);
		$this->assertNotEmpty($response['data']['team']['id']);
		$this->assertNotNull($response['data']['team']['name']['original']);
		$this->assertNotNull($response['data']['team']['name']['official']);
		$this->assertNotNull($response['data']['team']['name']['short']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['id']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']['original']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']['short']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['id']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']['original']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']['short']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['date']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_FINISHED]['team']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['team']['id']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_FINISHED]['team']['name']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['team']['name']['original']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['team']['name']['short']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['date']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_FINISHED]['result']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['result']['score']['home']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['result']['score']['away']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['result']['penalty']['home']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['result']['penalty']['away']);
		$this->assertNotEmpty($response['data']['lastMatches']);
		$this->assertCount(5, $response['data']['lastMatches']);
		/**
		 * Read from Cache.
		 */
		$this->teamsMatchRepository->drop();
		$this->teamRepository->drop();
		$response = $this->json('GET', sprintf('/en/teams/favorite/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['team']);
		$this->assertNotEmpty($response['data']['team']['id']);
		$this->assertNotNull($response['data']['team']['name']['original']);
		$this->assertNotNull($response['data']['team']['name']['official']);
		$this->assertNotNull($response['data']['team']['name']['short']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['id']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']['original']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']['short']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['id']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']['original']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']['short']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['date']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_FINISHED]['team']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['team']['id']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_FINISHED]['team']['name']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['team']['name']['original']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['team']['name']['short']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['date']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_FINISHED]['result']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['result']['score']['home']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['result']['score']['away']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['result']['penalty']['home']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['result']['penalty']['away']);
		$this->assertNotEmpty($response['data']['lastMatches']);
		$this->assertCount(5, $response['data']['lastMatches']);
	}

	public function testFavoriteWhenTeamNotExist()
	{
		$teamId = $this->faker->uuid;
		$response = $this->json('GET', sprintf('/en/teams/favorite/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertNotNull($response['message']);
		$this->assertEquals(config('common.error_codes.resource_not_found'), $response['code']);
	}

	public function testFavoriteWithoutUpcoming()
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
		/**
		 * Read from DB.
		 */
		$this->TeamsMatchCacheService->flush();
		$response = $this->json('GET', sprintf('/en/teams/favorite/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['team']);
		$this->assertNotEmpty($response['data']['team']['id']);
		$this->assertNotNull($response['data']['team']['name']['original']);
		$this->assertNotNull($response['data']['team']['name']['official']);
		$this->assertNotNull($response['data']['team']['name']['short']);
		$this->assertEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_FINISHED]['team']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['team']['id']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_FINISHED]['team']['name']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['team']['name']['original']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['team']['name']['short']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['date']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_FINISHED]['result']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['result']['score']['home']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['result']['score']['away']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['result']['penalty']['home']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['result']['penalty']['away']);
		$this->assertNotEmpty($response['data']['lastMatches']);
		$this->assertCount(5, $response['data']['lastMatches']);
		/**
		 * Read from Cache.
		 */
		$this->teamsMatchRepository->drop();
		$this->teamRepository->drop();
		$response = $this->json('GET', sprintf('/en/teams/favorite/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['team']);
		$this->assertNotEmpty($response['data']['team']['id']);
		$this->assertNotNull($response['data']['team']['name']['original']);
		$this->assertNotNull($response['data']['team']['name']['official']);
		$this->assertNotNull($response['data']['team']['name']['short']);
		$this->assertEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_FINISHED]['team']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['team']['id']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_FINISHED]['team']['name']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['team']['name']['original']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['team']['name']['short']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['date']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_FINISHED]['result']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['result']['score']['home']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['result']['score']['away']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['result']['penalty']['home']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_FINISHED]['result']['penalty']['away']);
		$this->assertNotEmpty($response['data']['lastMatches']);
		$this->assertCount(5, $response['data']['lastMatches']);
	}

	public function testFavoriteWithoutFinished()
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
		/**
		 * Read from DB.
		 */
		$this->TeamsMatchCacheService->flush();
		$response = $this->json('GET', sprintf('/en/teams/favorite/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['team']);
		$this->assertNotEmpty($response['data']['team']['id']);
		$this->assertNotNull($response['data']['team']['name']['original']);
		$this->assertNotNull($response['data']['team']['name']['official']);
		$this->assertNotNull($response['data']['team']['name']['short']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['id']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']['original']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']['short']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['id']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']['original']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']['short']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['date']);
		$this->assertEmpty($response['data'][TeamsMatch::STATUS_FINISHED]);
		$this->assertEmpty($response['data']['lastMatches']);
		/**
		 * Read from Cache.
		 */
		$this->teamsMatchRepository->drop();
		$this->teamRepository->drop();
		$response = $this->json('GET', sprintf('/en/teams/favorite/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['team']);
		$this->assertNotEmpty($response['data']['team']['id']);
		$this->assertNotNull($response['data']['team']['name']['original']);
		$this->assertNotNull($response['data']['team']['name']['official']);
		$this->assertNotNull($response['data']['team']['name']['short']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['id']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']['original']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['home']['name']['short']);
		$this->assertNotEmpty($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['id']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']['original']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['team']['away']['name']['short']);
		$this->assertNotNull($response['data'][TeamsMatch::STATUS_UPCOMING]['date']);
		$this->assertEmpty($response['data'][TeamsMatch::STATUS_FINISHED]);
		$this->assertEmpty($response['data']['lastMatches']);
	}

	protected function tearDown(): void
	{
		$this->teamsMatchRepository->drop();
	}
}