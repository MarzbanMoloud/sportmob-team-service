<?php


namespace Tests\Unit\Transfer;


use App\Models\ReadModels\Transfer;
use App\Models\Repositories\TransferRepository;
use DateTimeImmutable;
use TestCase;
use Faker\Factory;
use Tests\Traits\TransferRepositoryTestTrait;


/**
 * Class TransferRepositoryTest
 * @package Tests\Unit\Transfer
 */
class TransferRepositoryTest extends TestCase
{
	use TransferRepositoryTestTrait;

	private TransferRepository $transferRepository;
	private \Faker\Generator $faker;

	protected function setUp(): void
	{
		$this->createApplication();
		$this->faker = Factory::create();
		$this->transferRepository = app(TransferRepository::class);
		$this->createTransferTable();
	}

	public function testFindByPlayerId()
	{
		$fakeTransferModel = $this->createTransferModel();
		$fakeTransferModel->prePersist();
		$this->transferRepository->persist($fakeTransferModel);
		$response = $this->transferRepository->findByPlayerId($fakeTransferModel->getPlayerId());
		$this->assertInstanceOf(Transfer::class, $response[0]);
	}

	public function testFindByPlayerIdWhenItemNotExist()
	{
		$response = $this->transferRepository->findByPlayerId($this->faker->uuid);
		$this->assertEmpty($response);
	}

	public function testFindActiveTransfer()
	{
		$fakeTransferModel = $this->createTransferModel();
		$fakeTransferModel->prePersist();
		$this->transferRepository->persist($fakeTransferModel);
		$response = $this->transferRepository->findActiveTransfer($fakeTransferModel->getPlayerId());
		$this->assertInstanceOf(Transfer::class, $response[0]);
	}

	public function testFindActiveTransferWhenItemNotExist()
	{
		$response = $this->transferRepository->findActiveTransfer($this->faker->uuid);
		$this->assertEmpty($response);
	}

	public function testFindKnownPlayer()
	{
		$fakeTransferModel = $this->createTransferModel();
		$fakeTransferModel->prePersist();
		$fakeTransferModel->setPlayerName(null);
		$this->transferRepository->persist($fakeTransferModel);

		$fakePlayerId = $this->faker->uuid;
		$fakeTransferModel = $this->createTransferModel();
		$fakeTransferModel->prePersist();
		$fakeTransferModel->setPlayerId($fakePlayerId)->setPlayerName(null);
		$this->transferRepository->persist($fakeTransferModel);

		$fakeTransferModel = $this->createTransferModel();
		$fakeTransferModel->prePersist();
		$fakeTransferModel->setPlayerId($fakePlayerId)->setPlayerName($this->faker->name);
		$this->transferRepository->persist($fakeTransferModel);

		$response = $this->transferRepository->findKnownPlayer($fakePlayerId);
		$this->assertInstanceOf(Transfer::class, $response[0]);
	}

	public function testFindKnownPlayerWhenItemNotExist()
	{
		$fakeTransferModel = $this->createTransferModel();
		$fakeTransferModel->prePersist();
		$fakeTransferModel->setPlayerName(null);
		$this->transferRepository->persist($fakeTransferModel);

		$fakePlayerId = $this->faker->uuid;
		$fakeTransferModel = $this->createTransferModel();
		$fakeTransferModel->prePersist();
		$fakeTransferModel->setPlayerId($fakePlayerId)->setPlayerName(null);
		$this->transferRepository->persist($fakeTransferModel);

		$response = $this->transferRepository->findKnownPlayer($fakePlayerId);
		$this->assertEmpty($response);
	}

	public function testGetTransfersByTeamId()
	{
		$fakeTeamId = $this->faker->uuid;
		$fakeStatDates = ['2020-02-05', '2020-02-06', '2020-02-07', '2020-02-08'];
		foreach ($fakeStatDates as $key => $fakeStatDate) {
			$fakeTransferModel = $this->createTransferModel();
			$fakeTransferModel->prePersist();
			$fakeTransferModel
				->setStartDate(new DateTimeImmutable($fakeStatDate))
				->setPlayerName(null);
			if (in_array($key, [0, 2, 3])) {
				$fakeTransferModel->setFromTeamId($fakeTeamId);
			}
			$this->transferRepository->persist($fakeTransferModel);
		}
		$fakeTransferModel = $this->createTransferModel();
		$fakeTransferModel->setStartDate(new DateTimeImmutable());
		$fakeTransferModel->prePersist();
		$this->transferRepository->persist($fakeTransferModel);

		$this->assertCount(5, $this->transferRepository->findAll());
		$this->assertCount(3, $this->transferRepository->findByTeamId($fakeTeamId,'2019-2020'));
	}

	public function testGetTransfersByTeamIdWhenItemNotExist()
	{
		$this->assertEmpty($this->transferRepository->findByTeamId($this->faker->uuid,'2019-2020'));
	}

	public function testGetAllSeasons()
	{
		$fakeTransferModel = $this->createTransferModel();
		$fakeTransferModel->prePersist();
		$this->transferRepository->persist($fakeTransferModel);
		$response = $this->transferRepository->getAllSeasons($fakeTransferModel->getFromTeamId());
		$this->assertNotNull($response[0]);
	}

	public function testGetAllSeasonsWhenItemsNotExist()
	{
		$response = $this->transferRepository->getAllSeasons($this->faker->uuid);
		$this->assertEmpty($response);
	}

	protected function tearDown(): void
	{
		$this->transferRepository->drop();
	}
}