<?php


namespace Tests\Feature\TeamsMatch;


use App\ValueObjects\ReadModel\TeamName;
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
		$response = $this->json('GET', sprintf('/tm/en/overview/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['nextMatch']);
		$this->assertNotNull($response['data']['nextMatch']['id']);
		$this->assertNotEmpty($response['data']['nextMatch']['competition']);
		$this->assertNotNull($response['data']['nextMatch']['competition']['id']);
		$this->assertNotNull($response['data']['nextMatch']['competition']['name']);
		$this->assertNotEmpty($response['data']['nextMatch']['homeTeam']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['id']);
		$this->assertNotEmpty($response['data']['nextMatch']['homeTeam']['name']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['name']['full']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['name']['short']);
		$this->assertNotEmpty($response['data']['nextMatch']['awayTeam']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['id']);
		$this->assertNotEmpty($response['data']['nextMatch']['awayTeam']['name']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['name']['full']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['name']['short']);
		$this->assertNotNull($response['data']['nextMatch']['date']);
		$this->assertNotNull($response['data']['nextMatch']['coverage']);

		$this->assertNotEmpty($response['data']['teamForm']);
		$this->assertNotEmpty($response['data']['teamForm']['team']);
		$this->assertNotNull($response['data']['teamForm']['team']['id']);
		$this->assertNotEmpty($response['data']['teamForm']['team']['name']);
		$this->assertNotNull($response['data']['teamForm']['team']['name']['full']);
		$this->assertNotNull($response['data']['teamForm']['team']['name']['short']);
		$this->assertNotNull($response['data']['teamForm']['team']['name']['official']);
		foreach ($response['data']['teamForm']['form'] as $item) {
			$this->assertNotNull($item['id']);
			$this->assertNotEmpty($item['homeTeam']);
			$this->assertNotNull($item['homeTeam']['id']);
			$this->assertNotEmpty($item['homeTeam']['name']);
			$this->assertNotNull($item['homeTeam']['name']['full']);
			$this->assertNotNull($item['homeTeam']['name']['short']);
			$this->assertNotEmpty($item['awayTeam']);
			$this->assertNotNull($item['awayTeam']['id']);
			$this->assertNotEmpty($item['awayTeam']['name']);
			$this->assertNotNull($item['awayTeam']['name']['full']);
			$this->assertNotNull($item['awayTeam']['name']['short']);
			$this->assertNotEmpty($item['competition']);
			$this->assertNotNull($item['competition']['id']);
			$this->assertNotNull($item['competition']['name']);
			$this->assertNotNull($item['date']);
			$this->assertNotNull($item['coverage']);
			$this->assertNotEmpty($item['result']);
			$this->assertNotNull($item['result']['total']['home']);
			$this->assertNotNull($item['result']['total']['away']);
			$this->assertNotNull($item['result']['penalty']['home']);
			$this->assertNotNull($item['result']['penalty']['away']);
		}
		/**
		 * Read from Cache.
		 */
		$this->teamsMatchRepository->drop();
		$response = $this->json('GET', sprintf('/tm/en/overview/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['nextMatch']);
		$this->assertNotNull($response['data']['nextMatch']['id']);
		$this->assertNotEmpty($response['data']['nextMatch']['competition']);
		$this->assertNotNull($response['data']['nextMatch']['competition']['id']);
		$this->assertNotNull($response['data']['nextMatch']['competition']['name']);
		$this->assertNotEmpty($response['data']['nextMatch']['homeTeam']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['id']);
		$this->assertNotEmpty($response['data']['nextMatch']['homeTeam']['name']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['name']['full']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['name']['short']);
		$this->assertNotEmpty($response['data']['nextMatch']['awayTeam']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['id']);
		$this->assertNotEmpty($response['data']['nextMatch']['awayTeam']['name']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['name']['full']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['name']['short']);
		$this->assertNotNull($response['data']['nextMatch']['date']);
		$this->assertNotNull($response['data']['nextMatch']['coverage']);

		$this->assertNotEmpty($response['data']['teamForm']);
		$this->assertNotEmpty($response['data']['teamForm']['team']);
		$this->assertNotNull($response['data']['teamForm']['team']['id']);
		$this->assertNotEmpty($response['data']['teamForm']['team']['name']);
		$this->assertNotNull($response['data']['teamForm']['team']['name']['full']);
		$this->assertNotNull($response['data']['teamForm']['team']['name']['short']);
		$this->assertNotNull($response['data']['teamForm']['team']['name']['official']);
		foreach ($response['data']['teamForm']['form'] as $item) {
			$this->assertNotNull($item['id']);
			$this->assertNotEmpty($item['homeTeam']);
			$this->assertNotNull($item['homeTeam']['id']);
			$this->assertNotEmpty($item['homeTeam']['name']);
			$this->assertNotNull($item['homeTeam']['name']['full']);
			$this->assertNotNull($item['homeTeam']['name']['short']);
			$this->assertNotEmpty($item['awayTeam']);
			$this->assertNotNull($item['awayTeam']['id']);
			$this->assertNotEmpty($item['awayTeam']['name']);
			$this->assertNotNull($item['awayTeam']['name']['full']);
			$this->assertNotNull($item['awayTeam']['name']['short']);
			$this->assertNotEmpty($item['competition']);
			$this->assertNotNull($item['competition']['id']);
			$this->assertNotNull($item['competition']['name']);
			$this->assertNotNull($item['date']);
			$this->assertNotNull($item['coverage']);
			$this->assertNotEmpty($item['result']);
			$this->assertNotNull($item['result']['total']['home']);
			$this->assertNotNull($item['result']['total']['away']);
			$this->assertNotNull($item['result']['penalty']['home']);
			$this->assertNotNull($item['result']['penalty']['away']);
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
		$response = $this->json('GET', sprintf('/tm/en/overview/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertEmpty($response['data']['nextMatch']);

		$this->assertNotEmpty($response['data']['teamForm']);
		$this->assertNotEmpty($response['data']['teamForm']['team']);
		$this->assertNotNull($response['data']['teamForm']['team']['id']);
		$this->assertNotEmpty($response['data']['teamForm']['team']['name']);
		$this->assertNotNull($response['data']['teamForm']['team']['name']['full']);
		$this->assertNotNull($response['data']['teamForm']['team']['name']['short']);
		$this->assertNotNull($response['data']['teamForm']['team']['name']['official']);
		foreach ($response['data']['teamForm']['form'] as $item) {
			$this->assertNotNull($item['id']);
			$this->assertNotEmpty($item['homeTeam']);
			$this->assertNotNull($item['homeTeam']['id']);
			$this->assertNotEmpty($item['homeTeam']['name']);
			$this->assertNotNull($item['homeTeam']['name']['full']);
			$this->assertNotNull($item['homeTeam']['name']['short']);
			$this->assertNotEmpty($item['awayTeam']);
			$this->assertNotNull($item['awayTeam']['id']);
			$this->assertNotEmpty($item['awayTeam']['name']);
			$this->assertNotNull($item['awayTeam']['name']['full']);
			$this->assertNotNull($item['awayTeam']['name']['short']);
			$this->assertNotEmpty($item['competition']);
			$this->assertNotNull($item['competition']['id']);
			$this->assertNotNull($item['competition']['name']);
			$this->assertNotNull($item['date']);
			$this->assertNotEmpty($item['result']);
			$this->assertNotNull($item['result']['total']['home']);
			$this->assertNotNull($item['result']['total']['away']);
			$this->assertNotNull($item['result']['penalty']['home']);
			$this->assertNotNull($item['result']['penalty']['away']);
		}
		/**
		 * Read from Cache.
		 */
		$this->teamsMatchRepository->drop();
		$response = $this->json('GET', sprintf('/tm/en/overview/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertEmpty($response['data']['nextMatch']);

		$this->assertNotEmpty($response['data']['teamForm']);
		$this->assertNotEmpty($response['data']['teamForm']['team']);
		$this->assertNotNull($response['data']['teamForm']['team']['id']);
		$this->assertNotEmpty($response['data']['teamForm']['team']['name']);
		$this->assertNotNull($response['data']['teamForm']['team']['name']['full']);
		$this->assertNotNull($response['data']['teamForm']['team']['name']['short']);
		$this->assertNotNull($response['data']['teamForm']['team']['name']['official']);
		foreach ($response['data']['teamForm']['form'] as $item) {
			$this->assertNotNull($item['id']);
			$this->assertNotEmpty($item['homeTeam']);
			$this->assertNotNull($item['homeTeam']['id']);
			$this->assertNotEmpty($item['homeTeam']['name']);
			$this->assertNotNull($item['homeTeam']['name']['full']);
			$this->assertNotNull($item['homeTeam']['name']['short']);
			$this->assertNotEmpty($item['awayTeam']);
			$this->assertNotNull($item['awayTeam']['id']);
			$this->assertNotEmpty($item['awayTeam']['name']);
			$this->assertNotNull($item['awayTeam']['name']['full']);
			$this->assertNotNull($item['awayTeam']['name']['short']);
			$this->assertNotEmpty($item['competition']);
			$this->assertNotNull($item['competition']['id']);
			$this->assertNotNull($item['competition']['name']);
			$this->assertNotNull($item['date']);
			$this->assertNotEmpty($item['result']);
			$this->assertNotNull($item['result']['total']['home']);
			$this->assertNotNull($item['result']['total']['away']);
			$this->assertNotNull($item['result']['penalty']['home']);
			$this->assertNotNull($item['result']['penalty']['away']);
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
		$response = $this->json('GET', sprintf('/tm/en/overview/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['nextMatch']);
		$this->assertNotEmpty($response['data']['nextMatch']['competition']);
		$this->assertNotNull($response['data']['nextMatch']['competition']['id']);
		$this->assertNotNull($response['data']['nextMatch']['competition']['name']);
		$this->assertNotEmpty($response['data']['nextMatch']['homeTeam']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['id']);
		$this->assertNotEmpty($response['data']['nextMatch']['homeTeam']['name']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['name']['full']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['name']['short']);
		$this->assertNotEmpty($response['data']['nextMatch']['awayTeam']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['id']);
		$this->assertNotEmpty($response['data']['nextMatch']['awayTeam']['name']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['name']['full']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['name']['short']);
		$this->assertNotNull($response['data']['nextMatch']['date']);
		$this->assertNotNull($response['data']['nextMatch']['coverage']);

		$this->assertNotEmpty($response['data']['teamForm']);
		$this->assertNotEmpty($response['data']['teamForm']['team']);
		$this->assertNotNull($response['data']['teamForm']['team']['id']);
		$this->assertNotEmpty($response['data']['teamForm']['team']['name']);
		$this->assertNotNull($response['data']['teamForm']['team']['name']['full']);
		$this->assertNotNull($response['data']['teamForm']['team']['name']['short']);
		$this->assertNotNull($response['data']['teamForm']['team']['name']['official']);

		/**
		 * Read from Cache.
		 */
		$this->teamsMatchRepository->drop();
		$response = $this->json('GET', sprintf('/tm/en/overview/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['nextMatch']);
		$this->assertNotEmpty($response['data']['nextMatch']['competition']);
		$this->assertNotNull($response['data']['nextMatch']['competition']['id']);
		$this->assertNotNull($response['data']['nextMatch']['competition']['name']);
		$this->assertNotEmpty($response['data']['nextMatch']['homeTeam']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['id']);
		$this->assertNotEmpty($response['data']['nextMatch']['homeTeam']['name']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['name']['full']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['name']['short']);
		$this->assertNotEmpty($response['data']['nextMatch']['awayTeam']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['id']);
		$this->assertNotEmpty($response['data']['nextMatch']['awayTeam']['name']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['name']['full']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['name']['short']);
		$this->assertNotNull($response['data']['nextMatch']['date']);
		$this->assertNotNull($response['data']['nextMatch']['coverage']);

		$this->assertNotEmpty($response['data']['teamForm']);
		$this->assertNotEmpty($response['data']['teamForm']['team']);
		$this->assertNotNull($response['data']['teamForm']['team']['id']);
		$this->assertNotEmpty($response['data']['teamForm']['team']['name']);
		$this->assertNotNull($response['data']['teamForm']['team']['name']['full']);
		$this->assertNotNull($response['data']['teamForm']['team']['name']['short']);
		$this->assertNotNull($response['data']['teamForm']['team']['name']['official']);
	}

	public function testOverviewWhenTeamNotExist()
	{
		$teamId = $this->faker->uuid;
		$response = $this->json('GET', sprintf('/tm/en/overview/%s', $teamId));
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
		$response = $this->json('GET', sprintf('/tm/en/favorite/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['nextMatch']);
		$this->assertNotNull($response['data']['nextMatch']['id']);
		$this->assertNotEmpty($response['data']['nextMatch']['homeTeam']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['id']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['name']['full']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['name']['short']);
		$this->assertNotEmpty($response['data']['nextMatch']['awayTeam']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['id']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['name']['full']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['name']['short']);
		$this->assertNotNull($response['data']['nextMatch']['date']);
		$this->assertNotNull($response['data']['previousMatch']['team']['id']);
		$this->assertNotNull($response['data']['previousMatch']['team']['name']['full']);
		$this->assertNotNull($response['data']['previousMatch']['team']['name']['short']);
		$this->assertNotNull($response['data']['previousMatch']['team']['name']['official']);
		$this->assertNotNull($response['data']['previousMatch']['form']['id']);
		$this->assertNotNull($response['data']['previousMatch']['form']['homeTeam']['id']);
		$this->assertNotNull($response['data']['previousMatch']['form']['homeTeam']['name']['full']);
		$this->assertNotNull($response['data']['previousMatch']['form']['homeTeam']['name']['short']);
		$this->assertNotNull($response['data']['previousMatch']['form']['awayTeam']['id']);
		$this->assertNotNull($response['data']['previousMatch']['form']['awayTeam']['name']['full']);
		$this->assertNotNull($response['data']['previousMatch']['form']['awayTeam']['name']['short']);
		$this->assertNotNull($response['data']['previousMatch']['form']['competition']['id']);
		$this->assertNotNull($response['data']['previousMatch']['form']['competition']['name']);
		$this->assertNotNull($response['data']['previousMatch']['form']['date']);
		$this->assertNotNull($response['data']['previousMatch']['form']['coverage']);
		$this->assertNotNull($response['data']['previousMatch']['form']['result']['total']['home']);
		$this->assertNotNull($response['data']['previousMatch']['form']['result']['total']['away']);
		$this->assertNotNull($response['data']['previousMatch']['form']['result']['penalty']['away']);
		$this->assertNotNull($response['data']['previousMatch']['form']['result']['penalty']['home']);
		$this->assertNotNull($response['data']['teamFormSymbols']['team']['id']);
		$this->assertNotNull($response['data']['teamFormSymbols']['team']['name']['full']);
		$this->assertNotNull($response['data']['teamFormSymbols']['team']['name']['short']);
		$this->assertNotNull($response['data']['teamFormSymbols']['team']['name']['official']);
		$this->assertCount(5, $response['data']['teamFormSymbols']['form']);


		/**
		 * Read from Cache.
		 */
		$this->teamsMatchRepository->drop();
		$this->teamRepository->drop();
		$response = $this->json('GET', sprintf('/tm/en/favorite/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['nextMatch']);
		$this->assertNotNull($response['data']['nextMatch']['id']);
		$this->assertNotEmpty($response['data']['nextMatch']['homeTeam']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['id']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['name']['full']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['name']['short']);
		$this->assertNotEmpty($response['data']['nextMatch']['awayTeam']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['id']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['name']['full']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['name']['short']);
		$this->assertNotNull($response['data']['nextMatch']['date']);
		$this->assertNotNull($response['data']['previousMatch']['team']['id']);
		$this->assertNotNull($response['data']['previousMatch']['team']['name']['full']);
		$this->assertNotNull($response['data']['previousMatch']['team']['name']['short']);
		$this->assertNotNull($response['data']['previousMatch']['team']['name']['official']);
		$this->assertNotNull($response['data']['previousMatch']['form']['id']);
		$this->assertNotNull($response['data']['previousMatch']['form']['homeTeam']['id']);
		$this->assertNotNull($response['data']['previousMatch']['form']['homeTeam']['name']['full']);
		$this->assertNotNull($response['data']['previousMatch']['form']['homeTeam']['name']['short']);
		$this->assertNotNull($response['data']['previousMatch']['form']['awayTeam']['id']);
		$this->assertNotNull($response['data']['previousMatch']['form']['awayTeam']['name']['full']);
		$this->assertNotNull($response['data']['previousMatch']['form']['awayTeam']['name']['short']);
		$this->assertNotNull($response['data']['previousMatch']['form']['competition']['id']);
		$this->assertNotNull($response['data']['previousMatch']['form']['competition']['name']);
		$this->assertNotNull($response['data']['previousMatch']['form']['date']);
		$this->assertNotNull($response['data']['previousMatch']['form']['coverage']);
		$this->assertNotNull($response['data']['previousMatch']['form']['result']['total']['home']);
		$this->assertNotNull($response['data']['previousMatch']['form']['result']['total']['away']);
		$this->assertNotNull($response['data']['previousMatch']['form']['result']['penalty']['away']);
		$this->assertNotNull($response['data']['previousMatch']['form']['result']['penalty']['home']);
		$this->assertNotNull($response['data']['teamFormSymbols']['team']['id']);
		$this->assertNotNull($response['data']['teamFormSymbols']['team']['name']['full']);
		$this->assertNotNull($response['data']['teamFormSymbols']['team']['name']['short']);
		$this->assertNotNull($response['data']['teamFormSymbols']['team']['name']['official']);
		$this->assertCount(5, $response['data']['teamFormSymbols']['form']);
	}

	public function testFavoriteWhenTeamNotExist()
	{
		$teamId = $this->faker->uuid;
		$response = $this->json('GET', sprintf('/tm/en/favorite/%s', $teamId));
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
		$response = $this->json('GET', sprintf('/tm/en/favorite/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotNull($response['data']['previousMatch']['team']['id']);
		$this->assertNotNull($response['data']['previousMatch']['team']['name']['full']);
		$this->assertNotNull($response['data']['previousMatch']['team']['name']['short']);
		$this->assertNotNull($response['data']['previousMatch']['team']['name']['official']);
		$this->assertNotNull($response['data']['previousMatch']['form']['id']);
		$this->assertNotNull($response['data']['previousMatch']['form']['homeTeam']['id']);
		$this->assertNotNull($response['data']['previousMatch']['form']['homeTeam']['name']['full']);
		$this->assertNotNull($response['data']['previousMatch']['form']['homeTeam']['name']['short']);
		$this->assertNotNull($response['data']['previousMatch']['form']['awayTeam']['id']);
		$this->assertNotNull($response['data']['previousMatch']['form']['awayTeam']['name']['full']);
		$this->assertNotNull($response['data']['previousMatch']['form']['awayTeam']['name']['short']);
		$this->assertNotNull($response['data']['previousMatch']['form']['competition']['id']);
		$this->assertNotNull($response['data']['previousMatch']['form']['competition']['name']);
		$this->assertNotNull($response['data']['previousMatch']['form']['date']);
		$this->assertNotNull($response['data']['previousMatch']['form']['coverage']);
		$this->assertNotNull($response['data']['previousMatch']['form']['result']['total']['home']);
		$this->assertNotNull($response['data']['previousMatch']['form']['result']['total']['away']);
		$this->assertNotNull($response['data']['previousMatch']['form']['result']['penalty']['away']);
		$this->assertNotNull($response['data']['previousMatch']['form']['result']['penalty']['home']);
		$this->assertNotNull($response['data']['teamFormSymbols']['team']['id']);
		$this->assertNotNull($response['data']['teamFormSymbols']['team']['name']['full']);
		$this->assertNotNull($response['data']['teamFormSymbols']['team']['name']['short']);
		$this->assertNotNull($response['data']['teamFormSymbols']['team']['name']['official']);
		$this->assertCount(5, $response['data']['teamFormSymbols']['form']);
		/**
		 * Read from Cache.
		 */
		$this->teamsMatchRepository->drop();
		$this->teamRepository->drop();
		$response = $this->json('GET', sprintf('/tm/en/favorite/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotNull($response['data']['previousMatch']['team']['id']);
		$this->assertNotNull($response['data']['previousMatch']['team']['name']['full']);
		$this->assertNotNull($response['data']['previousMatch']['team']['name']['short']);
		$this->assertNotNull($response['data']['previousMatch']['team']['name']['official']);
		$this->assertNotNull($response['data']['previousMatch']['form']['id']);
		$this->assertNotNull($response['data']['previousMatch']['form']['homeTeam']['id']);
		$this->assertNotNull($response['data']['previousMatch']['form']['homeTeam']['name']['full']);
		$this->assertNotNull($response['data']['previousMatch']['form']['homeTeam']['name']['short']);
		$this->assertNotNull($response['data']['previousMatch']['form']['awayTeam']['id']);
		$this->assertNotNull($response['data']['previousMatch']['form']['awayTeam']['name']['full']);
		$this->assertNotNull($response['data']['previousMatch']['form']['awayTeam']['name']['short']);
		$this->assertNotNull($response['data']['previousMatch']['form']['competition']['id']);
		$this->assertNotNull($response['data']['previousMatch']['form']['competition']['name']);
		$this->assertNotNull($response['data']['previousMatch']['form']['date']);
		$this->assertNotNull($response['data']['previousMatch']['form']['coverage']);
		$this->assertNotNull($response['data']['previousMatch']['form']['result']['total']['home']);
		$this->assertNotNull($response['data']['previousMatch']['form']['result']['total']['away']);
		$this->assertNotNull($response['data']['previousMatch']['form']['result']['penalty']['away']);
		$this->assertNotNull($response['data']['previousMatch']['form']['result']['penalty']['home']);
		$this->assertNotNull($response['data']['teamFormSymbols']['team']['id']);
		$this->assertNotNull($response['data']['teamFormSymbols']['team']['name']['full']);
		$this->assertNotNull($response['data']['teamFormSymbols']['team']['name']['short']);
		$this->assertNotNull($response['data']['teamFormSymbols']['team']['name']['official']);
		$this->assertCount(5, $response['data']['teamFormSymbols']['form']);
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
		$response = $this->json('GET', sprintf('/tm/en/favorite/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['nextMatch']);
		$this->assertNotNull($response['data']['nextMatch']['id']);
		$this->assertNotEmpty($response['data']['nextMatch']['homeTeam']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['id']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['name']['full']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['name']['short']);
		$this->assertNotEmpty($response['data']['nextMatch']['awayTeam']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['id']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['name']['full']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['name']['short']);
		$this->assertNotNull($response['data']['nextMatch']['date']);
		/**
		 * Read from Cache.
		 */
		$this->teamsMatchRepository->drop();
		$this->teamRepository->drop();
		$response = $this->json('GET', sprintf('/tm/en/favorite/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertNotEmpty($response['data']['nextMatch']);
		$this->assertNotNull($response['data']['nextMatch']['id']);
		$this->assertNotEmpty($response['data']['nextMatch']['homeTeam']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['id']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['name']['full']);
		$this->assertNotNull($response['data']['nextMatch']['homeTeam']['name']['short']);
		$this->assertNotEmpty($response['data']['nextMatch']['awayTeam']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['id']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['name']['full']);
		$this->assertNotNull($response['data']['nextMatch']['awayTeam']['name']['short']);
		$this->assertNotNull($response['data']['nextMatch']['date']);
	}

	protected function tearDown(): void
	{
		$this->teamsMatchRepository->drop();
	}
}