<?php


namespace Tests\Unit\Trophy;


use App\Http\Services\Trophy\TrophyService;
use App\Models\ReadModels\Trophy;
use App\Models\Repositories\TrophyRepository;
use App\Services\Cache\Interfaces\TrophyCacheServiceInterface;
use Faker\Factory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TestCase;
use Tests\Traits\TrophyRepositoryTestTrait;


class TrophyServiceTest extends TestCase
{
	use TrophyRepositoryTestTrait;

	private \Faker\Generator $faker;
	private TrophyRepository $trophyRepository;
	private TrophyService $trophyService;
	private TrophyCacheServiceInterface $trophyCacheService;

	protected function setUp(): void
	{
		$this->createApplication();
		$this->faker = Factory::create();
		$this->trophyRepository = app(TrophyRepository::class);
		$this->trophyService = app(TrophyService::class);
		$this->trophyCacheService = app(TrophyCacheServiceInterface::class);
		$this->createTrophyTable();
	}

	public function testGetTrophiesByTeam()
	{
		[$teamId, ] = $this->persistBatchDataForTrophiesByTeam();
		$response = $this->trophyService->getTrophiesByTeam($teamId);
		$this->assertCount(16, $response);
		foreach ($response as $trophy) {
			$this->assertInstanceOf(Trophy::class, $trophy);
		}
	}

	public function testGetTrophiesByTeamWhenItemNotExist()
	{
		$response = $this->trophyService->getTrophiesByTeam($this->faker->uuid);
		$this->assertEmpty($response);
	}

	public function testGetTrophiesByCompetition()
	{
		[, $competitionId] = $this->persistBatchDataForTrophiesByTeam();
		$response = $this->trophyService->getTrophiesByCompetition($competitionId);
		$this->assertCount(8, $response);
		foreach ($response as $trophy) {
			$this->assertInstanceOf(Trophy::class, $trophy);
		}
	}

	public function testGetTrophiesByCompetitionWhenItemNotExist()
	{
		$this->expectException(NotFoundHttpException::class);
		$this->trophyService->getTrophiesByCompetition($this->faker->uuid);
	}

	protected function tearDown(): void
	{
		$this->trophyCacheService->flush();
		$this->trophyRepository->drop();
	}
}