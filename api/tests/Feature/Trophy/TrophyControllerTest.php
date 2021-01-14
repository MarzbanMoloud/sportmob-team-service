<?php


namespace Tests\Feature\Trophy;


use App\Models\Repositories\TrophyRepository;
use App\Services\Cache\Interfaces\TrophyCacheServiceInterface;
use Faker\Factory;
use TestCase;
use Tests\Traits\TrophyRepositoryTestTrait;


/**
 * Class TrophyControllerTest
 * @package Tests\Feature\Trophy
 */
class TrophyControllerTest extends TestCase
{
	use TrophyRepositoryTestTrait;

	private TrophyRepository $trophyRepository;
	private TrophyCacheServiceInterface $trophyCacheService;
	private \Faker\Generator $faker;

	protected function setUp(): void
	{
		parent::setUp();
		$this->createApplication();
		$this->trophyRepository = app(TrophyRepository::class);
		$this->trophyCacheService = app(TrophyCacheServiceInterface::class);
		$this->faker = Factory::create();
		$this->createTrophyTable();
	}

	public function testTrophiesByTeam()
	{
		[$teamId, ]= $this->persistBatchDataForTrophiesByTeam();
		/**
		 * Read from DB.
		 */
		$this->trophyCacheService->flush();
		$response = $this->json('GET', sprintf('/en/teams/trophies/team/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertCount(2, $response['data']);
		foreach ($response['data'] as $item) {
			$this->assertNotNull($item['competition']);
			$this->assertNotNull($item['competition']['id']);
			$this->assertNotNull($item['competition']['name']);
			$this->assertCount(4, $item['tournament']);
			foreach ($item['tournament'] as $tournaments) {
				$this->assertNotNull($tournaments['id']);
				$this->assertNotNull($tournaments['season']);
				$this->assertNotNull($tournaments['winner']);
				$this->assertNotNull($tournaments['winner']['id']);
				$this->assertNotNull($tournaments['winner']['name']);
				$this->assertNotNull($tournaments['runnerUp']['id']);
				$this->assertNotNull($tournaments['runnerUp']['name']);
			}
		}
		/**
		 * Read from Cache.
		 */
		$this->trophyRepository->drop();
		$response = $this->json('GET', sprintf('/en/teams/trophies/team/%s', $teamId));
		$response = json_decode($response->response->getContent(), true);
		$this->assertEmpty($response['links']);
		$this->assertNotEmpty($response['data']);
		$this->assertCount(2, $response['data']);
		foreach ($response['data'] as $item) {
			$this->assertNotNull($item['competition']);
			$this->assertNotNull($item['competition']['id']);
			$this->assertNotNull($item['competition']['name']);
			$this->assertCount(4, $item['tournament']);
			foreach ($item['tournament'] as $tournaments) {
				$this->assertNotNull($tournaments['id']);
				$this->assertNotNull($tournaments['season']);
				$this->assertNotNull($tournaments['winner']);
				$this->assertNotNull($tournaments['winner']['id']);
				$this->assertNotNull($tournaments['winner']['name']);
				$this->assertNotNull($tournaments['runnerUp']['id']);
				$this->assertNotNull($tournaments['runnerUp']['name']);
			}
		}
	}

	public function testTrophiesByTeamWhenItemNotExist()
	{
		$response = $this->json('GET', sprintf('/en/teams/trophies/team/%s', $this->faker->uuid));
		$response = json_decode($response->response->getContent(), true);
		$this->assertNotNull($response['message']);
		$this->assertEquals(config('common.error_codes.resource_not_found'), $response['code']);
	}

	public function testTrophiesByCompetition()
	{
		[, $competitionId]= $this->persistBatchDataForTrophiesByTeam();

		/**
		 * Read from DB.
		 */
		$this->trophyCacheService->flush();
		$response = $this->json('GET', sprintf('/en/teams/trophies/competition/%s', $competitionId));
		$response = json_decode($response->response->getContent(), true);
		foreach ($response['data'] as $item) {
			$this->assertNotNull($item['season']);
			$this->assertNotNull($item['id']);
			$this->assertNotNull($item['runnerUp']);
			$this->assertNotNull($item['runnerUp']['id']);
			$this->assertNotNull($item['winner']['id']);
			$this->assertNotNull($item['winner']['name']);
		}
		/**
		 * Read from Cache.
		 */
		$this->trophyRepository->drop();
		$response = $this->json('GET', sprintf('/en/teams/trophies/competition/%s', $competitionId));
		$response = json_decode($response->response->getContent(), true);
		foreach ($response['data'] as $item) {
			$this->assertNotNull($item['season']);
			$this->assertNotNull($item['id']);
			$this->assertNotNull($item['runnerUp']);
			$this->assertNotNull($item['runnerUp']['id']);
			$this->assertNotNull($item['winner']['id']);
			$this->assertNotNull($item['winner']['name']);
		}
	}

	public function testTrophiesByCompetitionWhenItemNotExist()
	{
		$response = $this->json('GET', sprintf('/en/teams/trophies/competition/%s', $this->faker->uuid));
		$response = json_decode($response->response->getContent(), true);
		$this->assertNotNull($response['message']);
		$this->assertEquals(config('common.error_codes.resource_not_found'), $response['code']);
	}

	protected function tearDown(): void
	{
		$this->trophyRepository->drop();
		$this->trophyCacheService->flush();
	}
}