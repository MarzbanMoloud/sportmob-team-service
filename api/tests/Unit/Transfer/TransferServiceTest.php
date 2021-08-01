<?php


namespace Tests\Unit\Transfer;


use App\Http\Services\Transfer\TransferService;
use App\Models\ReadModels\Transfer;
use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TransferRepository;
use App\Services\Cache\Interfaces\TransferCacheServiceInterface;
use Faker\Factory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use TestCase;
use Tests\Traits\TeamRepositoryTestTrait;
use Tests\Traits\TransferRepositoryTestTrait;


/**
 * Class TransferServiceTest
 * @package Tests\Unit\Transfer
 */
class TransferServiceTest extends TestCase
{
	use TransferRepositoryTestTrait, TeamRepositoryTestTrait;

	private \Faker\Generator $faker;
	private TransferRepository $transferRepository;
	private TransferService $transferService;
	private TransferCacheServiceInterface $transferCacheService;
	private SerializerInterface $serializer;
	private TeamRepository $teamRepository;

	protected function setUp(): void
	{
		$this->createApplication();
		$this->faker = Factory::create();
		$this->transferRepository = app(TransferRepository::class);
		$this->transferService = app(TransferService::class);
		$this->teamRepository = app(TeamRepository::class);
		$this->transferCacheService = app(TransferCacheServiceInterface::class);
		$this->serializer = app(SerializerInterface::class);
		$this->createTransferTable();
		$this->createTeamTable();
	}

	public function testListByTeam()
	{
		$fakeTeamId = $this->faker->uuid;
		$this->persistBatchDataForTeam($fakeTeamId);
		$response = $this->transferService->listByTeam($fakeTeamId, '2015-2016');
		$this->assertCount(2, $response['transfers']);
		foreach ($response['transfers'] as $transfer) {
			$this->assertInstanceOf(Transfer::class, $transfer);
		}
		$this->assertCount(8, $response['seasons']);
	}

	public function testListByTeamWithoutSeason()
	{
		$fakeTeamId = $this->faker->uuid;
		$this->persistBatchDataForTeam($fakeTeamId);
		$response = $this->transferService->listByTeam($fakeTeamId);
		$this->assertCount(1, $response['transfers']);
		foreach ($response['transfers'] as $transfer) {
			$this->assertInstanceOf(Transfer::class, $transfer);
		}
		$this->assertCount(8, $response['seasons']);
	}

	public function testListByTeamWhenItemNotExist()
	{
		$this->expectException(NotFoundHttpException::class);
		$response = $this->transferService->listByTeam($this->faker->uuid, '2020-2021');
		$this->assertEmpty($response);
	}

	public function testListByTeamWhenItemNotExistAndWithoutSeason()
	{
		$this->expectException(NotFoundHttpException::class);
		$fakeTeamId = $this->faker->uuid;
		$response = $this->transferService->listByTeam($fakeTeamId);
		foreach ($response as $transfer) {
			$this->assertInstanceOf(Transfer::class, $transfer);
		}
	}

	public function testListByPerson()
	{
		$fakePlayerId = $this->faker->uuid;
		$this->persistBatchDataForPerson($fakePlayerId);
		$response = $this->transferService->listByPerson($fakePlayerId);
		foreach ($response as $transfer) {
			$this->assertInstanceOf(Transfer::class, $transfer);
		}
	}

	public function testListByPlayerWhenItemNotExist()
	{
		$this->expectException(NotFoundHttpException::class);
		$this->transferService->listByPerson($this->faker->uuid);
	}

	protected function tearDown(): void
	{
		$this->transferRepository->drop();
		$this->transferCacheService->flush();
	}
}