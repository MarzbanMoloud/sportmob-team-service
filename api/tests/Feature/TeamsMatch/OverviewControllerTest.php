<?php


namespace Tests\Feature\TeamsMatch;


use App\Models\ReadModels\TeamsMatch;
use App\Models\Repositories\TeamsMatchRepository;
use App\Services\Cache\Interfaces\TeamsMatchCacheServiceInterface;
use Faker\Factory;
use TestCase;
use Tests\Traits\TeamsMatchRepositoryTestTrait;


/**
 * Class OverviewControllerTest
 * @package Tests\Feature\TeamsMatch
 */
class OverviewControllerTest extends TestCase
{
	use TeamsMatchRepositoryTestTrait;

	private TeamsMatchRepository $teamsMatchRepository;
	private \Faker\Generator $faker;
	private TeamsMatchCacheServiceInterface $TeamsMatchCacheService;

	protected function setUp(): void
	{
		parent::setUp();
		$this->createApplication();
		$this->teamsMatchRepository = app(TeamsMatchRepository::class);
		$this->TeamsMatchCacheService = app(TeamsMatchCacheServiceInterface::class);
		$this->faker = Factory::create();
		$this->createTeamsMatchTable();
	}

	/**
	 * @throws \Exception
	 */
	public function testIndex()
	{
		$teamId = $this->faker->uuid;
		$teamName = $this->faker->name;
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
			$this->assertNotNull($item['result']['home']);
			$this->assertNotNull($item['result']['away']);
		}
		/**
		 * Read from Cache.
		 */
		$this->teamsMatchRepository->drop();
		$response = $this->json('GET', sprintf('/en/teams/overview/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
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
			$this->assertNotNull($item['result']['home']);
			$this->assertNotNull($item['result']['away']);
		}
	}

	public function testIndexWithoutUpcoming()
	{
		$teamId = $this->faker->uuid;
		$teamName = $this->faker->name;
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
			$this->assertNotNull($item['result']['home']);
			$this->assertNotNull($item['result']['away']);
		}
		/**
		 * Read from Cache.
		 */
		$this->teamsMatchRepository->drop();
		$response = $this->json('GET', sprintf('/en/teams/overview/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
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
			$this->assertNotNull($item['result']['home']);
			$this->assertNotNull($item['result']['away']);
		}
	}

	public function testIndexWithoutFinished()
	{
		$teamId = $this->faker->uuid;
		$teamName = $this->faker->name;
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

	protected function tearDown(): void
	{
		$this->teamsMatchRepository->drop();
	}
}