<?php


namespace Tests\Unit\Transfer;


use App\Http\Services\Transfer\TransferService;
use App\Models\ReadModels\Transfer;
use App\Models\Repositories\TransferRepository;
use App\Services\Cache\Interfaces\TransferCacheServiceInterface;
use Faker\Factory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TestCase;
use Tests\Traits\TransferRepositoryTestTrait;


/**
 * Class TransferServiceTest
 * @package Tests\Unit\Transfer
 */
class TransferServiceTest extends TestCase
{
	use TransferRepositoryTestTrait;

	private \Faker\Generator $faker;
	private TransferRepository $transferRepository;
	private TransferService $transferService;
	private TransferCacheServiceInterface $transferCacheService;

	protected function setUp(): void
	{
		$this->createApplication();
		$this->faker = Factory::create();
		$this->transferRepository = app(TransferRepository::class);
		$this->transferService = app(TransferService::class);
		$this->transferCacheService = app(TransferCacheServiceInterface::class);
		$this->createTransferTable();
	}

	public function testListByTeam()
	{
		$fakeTeamId = $this->faker->uuid;
		$fakeTeamName = $this->faker->name;
		$this->persistBatchDataForListByTeam($fakeTeamId, $fakeTeamName);
		$response = $this->transferService->listByTeam($fakeTeamId, '2019-2020');
		foreach ($response as $transfer) {
			$this->assertInstanceOf(Transfer::class, $transfer);
		}
	}

	public function testListByTeamWithoutSeason()
	{
		$fakeTeamId = $this->faker->uuid;
		$fakeTeamName = $this->faker->name;
		$this->persistBatchDataForListByTeam($fakeTeamId, $fakeTeamName);
		$response = $this->transferService
			->setSeasons($this->transferService->getAllSeasons($fakeTeamId))
			->listByTeam($fakeTeamId);
		foreach ($response as $transfer) {
			$this->assertInstanceOf(Transfer::class, $transfer);
		}
	}

	public function testListByTeamWhenItemNotExist()
	{
		$response = $this->transferService->listByTeam($this->faker->uuid, '2020-2021');
		$this->assertEmpty($response);
	}

	public function testListByTeamWhenItemNotExistAndWithoutSeason()
	{
		$this->expectException(NotFoundHttpException::class);
		$fakeTeamId = $this->faker->uuid;
		$response = $this->transferService
			->setSeasons($this->transferService->getAllSeasons($fakeTeamId))
			->listByTeam($fakeTeamId);
		foreach ($response as $transfer) {
			$this->assertInstanceOf(Transfer::class, $transfer);
		}
	}

	public function testGetAllSeasons()
	{
		$fakeTeamId = $this->faker->uuid;
		$fakeTeamName = $this->faker->name;
		$this->persistBatchDataForListByTeam($fakeTeamId, $fakeTeamName);
		$response = $this->transferService->getAllSeasons($fakeTeamId);
		$this->assertNotEmpty($response);
		$this->assertCount(2, $response);
	}

	public function testGetAllSeasonsWhenItemNotExist()
	{
		$this->expectException(NotFoundHttpException::class);
		$this->transferService->getAllSeasons($this->faker->uuid);
	}

	public function testListByPlayer()
	{
		$fakePlayerId = $this->faker->uuid;
		$fakePlayerName = $this->faker->name;
		$this->persistBatchDataForListByPlayer($fakePlayerId, $fakePlayerName);
		$response = $this->transferService->listByPlayer($fakePlayerId);
		foreach ($response as $transfer) {
			$this->assertInstanceOf(Transfer::class, $transfer);
		}
	}

	public function testListByPlayerWhenItemNotExist()
	{
		$this->expectException(NotFoundHttpException::class);
		$this->transferService->listByPlayer($this->faker->uuid);
	}

	protected function tearDown(): void
	{
		$this->transferRepository->drop();
		$this->transferCacheService->flush();
	}
}