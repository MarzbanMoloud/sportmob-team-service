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
		$playerId = $this->faker->uuid;
		for ($i = 0; $i < 5; $i++) {
			$this->createTransferModel($playerId);
		}
		for ($i = 0; $i < 10; $i++) {
			$this->createTransferModel();
		}
		$response = $this->transferRepository->findByPlayerId($playerId);
		$this->assertCount(5, $response);

		foreach ($response as $transferItem) {
			/**
			 * @var Transfer $transferItem
			 */
			$this->assertInstanceOf(Transfer::class, $transferItem);
			$this->assertEquals($playerId, $transferItem->getPlayerId());
			$this->assertEquals("0", $transferItem->getSeason());
			$this->assertEquals(Transfer::getDateTimeImmutable()->getTimestamp(),
				$transferItem->getStartDate()->getTimestamp());
			$this->assertIsString($transferItem->getId());
		}
	}

	public function testFindByPlayerIdCheckWithStartDateAndSeason()
	{
		$playerId = $this->faker->uuid;
		$startDate = (new DateTimeImmutable())->setDate(2021, 01, 02)->setTime(0, 0, 0);
		for ($i = 0; $i < 5; $i++) {
			$this->createTransferModel($playerId, $startDate);
		}
		$response = $this->transferRepository->findByPlayerId($playerId);
		$this->assertCount(5, $response);

		foreach ($response as $transferItem) {
			/**
			 * @var Transfer $transferItem
			 */
			$this->assertInstanceOf(Transfer::class, $transferItem);
			$this->assertEquals($playerId, $transferItem->getPlayerId());
			$this->assertEquals("2020-2021", $transferItem->getSeason());
			$this->assertEquals($startDate->getTimestamp(), $transferItem->getStartDate()->getTimestamp());
		}
	}

	public function testFindByPlayerIdCheckWithStartDateAndSeason1()
	{
		$playerId = $this->faker->uuid;
		$startDate = (new DateTimeImmutable())->setDate(2021, 03, 02)->setTime(0, 0, 0);
		for ($i = 0; $i < 5; $i++) {
			$this->createTransferModel($playerId, $startDate);
		}
		$response = $this->transferRepository->findByPlayerId($playerId);
		$this->assertCount(5, $response);

		foreach ($response as $transferItem) {
			/**
			 * @var Transfer $transferItem
			 */
			$this->assertInstanceOf(Transfer::class, $transferItem);
			$this->assertEquals($playerId, $transferItem->getPlayerId());
			$this->assertEquals("2021-2022", $transferItem->getSeason());
			$this->assertEquals($startDate->getTimestamp(), $transferItem->getStartDate()->getTimestamp());
		}
	}

	public function testFindByPlayerIdWhenItemNotExist()
	{
		$response = $this->transferRepository->findByPlayerId($this->faker->uuid);
		$this->assertEmpty($response);
	}

	public function testFindActiveTransfer()
	{
		$playerId = uniqid();
		for ($i = 0; $i < 5; $i++) {
			$this->createTransferModel($playerId);
		}
		$this->createTransferModel($playerId, null, true);
		$response = $this->transferRepository->findActiveTransfer($playerId);
		$this->assertCount(1, $response);
		$this->assertInstanceOf(Transfer::class, $response[0]);
		$this->assertEquals(1, $response[0]->getActive());
		$this->assertEquals($playerId, $response[0]->getPlayerId());
	}

	public function testFindActiveTransferWhenActiveNotExists()
	{
		$playerId = uniqid();
		for ($i = 0; $i < 5; $i++) {
			$this->createTransferModel($playerId);
		}
		$response = $this->transferRepository->findActiveTransfer($playerId);
		$this->assertEmpty($response);
	}

	public function testFindActiveTransferWhenPlayerNotExist()
	{
		$response = $this->transferRepository->findActiveTransfer($this->faker->uuid);
		$this->assertEmpty($response);
	}

	public function testFindByTeamIdAndSeasonWithToTeamWithoutSeason()
	{
		$teamId = uniqid();
		$startDate2021 = (new DateTimeImmutable('2021-02-05'));
		$startDate2020 = (new DateTimeImmutable('2020-02-05'));
		$startDate2019 = (new DateTimeImmutable('2019-02-05'));

		for ($i = 0; $i < 5; $i++) {
			$this->createTransferModel(uniqid(), $startDate2021, false, $teamId);
		}

		for ($i = 0; $i < 3; $i++) {
			$this->createTransferModel(uniqid(), $startDate2020, false, $teamId);
		}

		for ($i = 0; $i < 2; $i++) {
			$this->createTransferModel(uniqid(), $startDate2019, false, $teamId);
		}

		for ($i = 0; $i < 5; $i++) {
			$this->createTransferModel(uniqid(), $startDate2019, false, uniqid(), $teamId);
		}

		$this->assertCount(15, $this->transferRepository->findAll());
		$result = $this->transferRepository->findByTeamIdAndSeason(Transfer::ATTR_TO_TEAM_ID, $teamId);
		$this->assertCount(10, $result);
		$s2020 = $s2019 = $s2018 = 0;
		/**
		 * @var Transfer $item
		 */
		foreach ($result as $item) {
			$this->assertEquals($teamId, $item->getToTeamId());
			$this->assertContains($item->getSeason(), ["2020-2021", "2019-2020", "2018-2019"]);
			if ($item->getSeason() == "2020-2021") {
				$s2020++;
				$this->assertEquals($startDate2021->getTimestamp(), $item->getStartDate()->getTimestamp());
			} elseif ($item->getSeason() == "2019-2020") {
				$s2019++;
				$this->assertEquals($startDate2020->getTimestamp(), $item->getStartDate()->getTimestamp());
			} else {
				$s2018++;
				$this->assertEquals($startDate2019->getTimestamp(), $item->getStartDate()->getTimestamp());
			}
		}
		$this->assertEquals(5, $s2020);
		$this->assertEquals(3, $s2019);
		$this->assertEquals(2, $s2018);
	}

	public function testFindByTeamIdAndSeasonWithToTeamAndSeason()
	{
		$teamId = uniqid();
		$startDate2021 = (new DateTimeImmutable('2021-02-05'));
		$startDate2020 = (new DateTimeImmutable('2020-02-05'));

		for ($i = 0; $i < 2; $i++) {
			$this->createTransferModel(uniqid(), $startDate2021, false, $teamId);
		}

		for ($i = 0; $i < 3; $i++) {
			$this->createTransferModel(uniqid(), $startDate2020, false, $teamId);
		}

		$this->assertCount(5, $this->transferRepository->findAll());
		$result = $this->transferRepository->findByTeamIdAndSeason(Transfer::ATTR_TO_TEAM_ID, $teamId, '2020-2021');

		$this->assertCount(2, $result);
		/**
		 * @var Transfer $item
		 */
		foreach ($result as $item) {
			$this->assertEquals($teamId, $item->getToTeamId());
			$this->assertEquals("2020-2021", $item->getSeason());
			$this->assertEquals($startDate2021->getTimestamp(), $item->getStartDate()->getTimestamp());
		}
	}

	public function testFindByTeamIdAndSeasonWithFromTeamWithoutSeason()
	{
		$teamId = uniqid();
		$startDate2021 = (new DateTimeImmutable('2021-02-05'));
		$startDate2020 = (new DateTimeImmutable('2020-02-05'));
		$startDate2019 = (new DateTimeImmutable('2019-02-05'));

		for ($i = 0; $i < 5; $i++) {
			$this->createTransferModel(uniqid(), $startDate2021, false, uniqid(), $teamId);
		}

		for ($i = 0; $i < 3; $i++) {
			$this->createTransferModel(uniqid(), $startDate2020, false, uniqid(), $teamId);
		}

		for ($i = 0; $i < 2; $i++) {
			$this->createTransferModel(uniqid(), $startDate2019, false, uniqid(), $teamId);
		}

		for ($i = 0; $i < 5; $i++) {
			$this->createTransferModel(uniqid(), $startDate2019, false, $teamId, uniqid());
		}

		$this->assertCount(15, $this->transferRepository->findAll());
		$result = $this->transferRepository->findByTeamIdAndSeason(Transfer::ATTR_FROM_TEAM_ID, $teamId);
		$this->assertCount(10, $result);
		$s2020 = $s2019 = $s2018 = 0;
		/**
		 * @var Transfer $item
		 */
		foreach ($result as $item) {
			$this->assertEquals($teamId, $item->getFromTeamId());
			$this->assertContains($item->getSeason(), ["2020-2021", "2019-2020", "2018-2019"]);
			if ($item->getSeason() == "2020-2021") {
				$s2020++;
				$this->assertEquals($startDate2021->getTimestamp(), $item->getStartDate()->getTimestamp());
			} elseif ($item->getSeason() == "2019-2020") {
				$s2019++;
				$this->assertEquals($startDate2020->getTimestamp(), $item->getStartDate()->getTimestamp());
			} else {
				$s2018++;
				$this->assertEquals($startDate2019->getTimestamp(), $item->getStartDate()->getTimestamp());
			}
		}
		$this->assertEquals(5, $s2020);
		$this->assertEquals(3, $s2019);
		$this->assertEquals(2, $s2018);
	}

	public function testFindByTeamIdAndSeasonWithFromTeamAndSeason()
	{
		$teamId = uniqid();
		$startDate2021 = (new DateTimeImmutable('2021-02-05'));
		$startDate2020 = (new DateTimeImmutable('2020-02-05'));

		for ($i = 0; $i < 2; $i++) {
			$this->createTransferModel(uniqid(), $startDate2021, false, uniqid(), $teamId);
		}

		for ($i = 0; $i < 3; $i++) {
			$this->createTransferModel(uniqid(), $startDate2020, false, uniqid(), $teamId);
		}

		$this->assertCount(5, $this->transferRepository->findAll());
		$result = $this->transferRepository->findByTeamIdAndSeason(Transfer::ATTR_FROM_TEAM_ID, $teamId, '2020-2021');

		$this->assertCount(2, $result);
		/**
		 * @var Transfer $item
		 */
		foreach ($result as $item) {
			$this->assertEquals($teamId, $item->getFromTeamId());
			$this->assertEquals("2020-2021", $item->getSeason());
			$this->assertEquals($startDate2021->getTimestamp(), $item->getStartDate()->getTimestamp());
		}
	}

	public function testFindByTeamIdAndSeasonWhenItemNotExists()
	{
		for ($i = 0; $i < 5; $i++) {
			$this->createTransferModel();
		}
		$result = $this->transferRepository->findByTeamIdAndSeason(Transfer::ATTR_FROM_TEAM_ID, uniqid(), '2020-2021');
		$this->assertEmpty($result);
	}

	protected function tearDown(): void
	{
		$this->transferRepository->drop();
	}
}