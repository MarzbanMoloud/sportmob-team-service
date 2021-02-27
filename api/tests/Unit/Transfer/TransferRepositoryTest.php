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
	static int $fakeStartYear = 1992;
	static int $fakeEndYear = 1993;

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
		$fakePlayerId = '';
		for ($i = 0; $i<10; $i++) {
			$fakeTransferModel = $this->createTransferModel();
			if ($i == 5) {
				$fakeTransferModel->setActive(true);
				$fakePlayerId = $fakeTransferModel->getPlayerId();
			} else {
				$fakeTransferModel->setActive(false);
			}
			$fakeTransferModel->prePersist();
			$this->transferRepository->persist($fakeTransferModel);
		}
		$response = $this->transferRepository->findActiveTransfer($fakePlayerId);
		$this->assertInstanceOf(Transfer::class, $response[0]);
	}

	public function testFindActiveTransferWhenItemNotExist()
	{
		$response = $this->transferRepository->findActiveTransfer($this->faker->uuid);
		$this->assertEmpty($response);
	}

	public function testFindByTeamIdAndSeason()
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
		$this->assertCount(3, $this->transferRepository->findByTeamIdAndSeason($fakeTeamId,'2019-2020'));
	}

	public function testFindByTeamIdAndSeasonWhenItemNotExist()
	{
		$this->assertEmpty($this->transferRepository->findByTeamIdAndSeason($this->faker->uuid,'2019-2020'));
	}

	public function testFindByTeamId()
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
		$this->assertCount(3, $this->transferRepository->findByTeamId($fakeTeamId));
	}

	public function testFindByTeamIdWhenItemNotExist()
	{
		$this->assertEmpty($this->transferRepository->findByTeamId($this->faker->uuid));
	}

	public function testGetAllSeasons()
	{
		$fromTeamId = $this->faker->uuid;
		$toTeamId = $this->faker->uuid;
		for ($i = 0; $i < 50; $i++) {
			self::$fakeStartYear+=1;
			self::$fakeEndYear+=1;
			$fakeTransferModel = $this->createTransferModel();
			$fakeTransferModel
				->setStartDate(new DateTimeImmutable(self::$fakeStartYear . '-01-01'))
				->setEndDate(new DateTimeImmutable(self::$fakeEndYear . '-01-01'))
				->setFromTeamId($fromTeamId)
				->setToTeamId($toTeamId);
			$fakeTransferModel->prePersist();
			$this->transferRepository->persist($fakeTransferModel);
		}
		$response = $this->transferRepository->getAllSeasons($toTeamId);
		$this->assertCount(50, $response);
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