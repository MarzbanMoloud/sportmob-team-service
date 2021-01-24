<?php


namespace Tests\Unit\Team;


use App\Http\Services\Team\TeamService;
use App\Models\ReadModels\Team;
use App\Models\Repositories\TeamRepository;
use App\Services\Cache\Interfaces\TeamCacheServiceInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TestCase;
use Faker\Factory;
use Tests\Traits\TeamRepositoryTestTrait;


class TeamServiceTest extends TestCase
{
	use TeamRepositoryTestTrait;

	private \Faker\Generator  $faker;
	private TeamRepository $teamRepository;
	private TeamService $teamService;
	private TeamCacheServiceInterface $teamCacheService;

	protected function setUp(): void
	{
		$this->createApplication();
		$this->teamRepository = app(TeamRepository::class);
		$this->teamService = app(TeamService::class);
		$this->teamCacheService = app(TeamCacheServiceInterface::class);
		$this->faker = Factory::create();
		$this->createTeamTable();
	}

	public function testFindTeamById()
	{
		$fakeTeamId = $this->faker->uuid;
		$fakeTeamModel = $this->createTeamModel();
		$fakeTeamModel->setId($fakeTeamId);
		$this->teamRepository->persist($fakeTeamModel);
		/**
		 * Read from DB.
		 */
		$response = $this->teamService->findTeamById($fakeTeamId);
		$this->assertInstanceOf(Team::class, $response);
		/**
		 * Read from Cache.
		 */
		$response = $this->teamService->findTeamById($fakeTeamId);
		$this->assertInstanceOf(Team::class, $response);
	}

	public function testFindTeamByIdWhenItemNotExist()
	{
		$this->expectException(NotFoundHttpException::class);
		$this->teamService->findTeamById($this->faker->uuid);
	}

	protected function tearDown(): void
	{
		$this->teamRepository->drop();
		$this->teamCacheService->flush();
	}
}