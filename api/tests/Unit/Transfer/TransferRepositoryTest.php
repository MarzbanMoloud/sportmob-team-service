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

	public function testFindByPersonId()
	{
		$personId = $this->faker->uuid;
		for ($i = 0; $i < 5; $i++) {
			$this->persistTransfer($personId);
		}
		for ($i = 0; $i < 10; $i++) {
			$this->persistTransfer();
		}
		$response = $this->transferRepository->findByPersonId($personId);
		$this->assertCount(5, $response);

		foreach ($response as $transferItem) {
			/**
			 * @var Transfer $transferItem
			 */
			$this->assertInstanceOf(Transfer::class, $transferItem);
			$this->assertEquals($personId, $transferItem->getPersonId());
			$this->assertEquals("0", $transferItem->getSeason());
			$this->assertEquals(Transfer::getDateTimeImmutable()->getTimestamp(),
				$transferItem->getDateFrom()->getTimestamp());
			$this->assertIsString($transferItem->getId());
		}
	}

	public function testFindByPersonIdCheckWithStartDateAndSeason()
	{
		$personId = $this->faker->uuid;
		$startDate = (new DateTimeImmutable())->setDate(2021, 01, 02)->setTime(0, 0, 0);
		for ($i = 0; $i < 5; $i++) {
			$this->persistTransfer($personId, $startDate);
		}
		$response = $this->transferRepository->findByPersonId($personId);
		$this->assertCount(5, $response);

		foreach ($response as $transferItem) {
			/**
			 * @var Transfer $transferItem
			 */
			$this->assertInstanceOf(Transfer::class, $transferItem);
			$this->assertEquals($personId, $transferItem->getPersonId());
			$this->assertEquals("2020-2021", $transferItem->getSeason());
			$this->assertEquals($startDate->getTimestamp(), $transferItem->getDateFrom()->getTimestamp());
		}
	}

	public function testFindByPersonIdCheckWithStartDateAndSeason1()
	{
		$personId = $this->faker->uuid;
		$startDate = (new DateTimeImmutable())->setDate(2021, 03, 02)->setTime(0, 0, 0);
		for ($i = 0; $i < 5; $i++) {
			$this->persistTransfer($personId, $startDate);
		}
		$response = $this->transferRepository->findByPersonId($personId);
		$this->assertCount(5, $response);

		foreach ($response as $transferItem) {
			/**
			 * @var Transfer $transferItem
			 */
			$this->assertInstanceOf(Transfer::class, $transferItem);
			$this->assertEquals($personId, $transferItem->getPersonId());
			$this->assertEquals("2021-2022", $transferItem->getSeason());
			$this->assertEquals($startDate->getTimestamp(), $transferItem->getDateFrom()->getTimestamp());
		}
	}

	public function testFindByPersonIdWhenItemNotExist()
	{
		$response = $this->transferRepository->findByPersonId($this->faker->uuid);
		$this->assertEmpty($response);
	}

	public function testFindByTeamIdAndSeasonWithTeamIdWithoutSeason()
	{
		$teamId = uniqid();
		$startDate2021 = (new DateTimeImmutable('2021-02-05'));
		$startDate2020 = (new DateTimeImmutable('2020-02-05'));
		$startDate2019 = (new DateTimeImmutable('2019-02-05'));

		for ($i = 0; $i < 5; $i++) {
			$this->persistTransfer(uniqid(), $startDate2021, $teamId);
		}

		for ($i = 0; $i < 3; $i++) {
			$this->persistTransfer(uniqid(), $startDate2020, $teamId);
		}

		for ($i = 0; $i < 2; $i++) {
			$this->persistTransfer(uniqid(), $startDate2019, $teamId);
		}

		for ($i = 0; $i < 5; $i++) {
			$this->persistTransfer(uniqid(), $startDate2019, uniqid(), $teamId);
		}

		$this->assertCount(15, $this->transferRepository->findAll());
		$result = $this->transferRepository->findAllByTeamIdAndSeason(Transfer::ATTR_TEAM_ID, $teamId);
		$this->assertCount(10, $result);
		$s2020 = $s2019 = $s2018 = 0;
		/**
		 * @var Transfer $item
		 */
		foreach ($result as $item) {
			$this->assertEquals($teamId, $item->getTeamId());
			$this->assertContains($item->getSeason(), ["2020-2021", "2019-2020", "2018-2019"]);
			if ($item->getSeason() == "2020-2021") {
				$s2020++;
				$this->assertEquals($startDate2021->getTimestamp(), $item->getDateFrom()->getTimestamp());
			} elseif ($item->getSeason() == "2019-2020") {
				$s2019++;
				$this->assertEquals($startDate2020->getTimestamp(), $item->getDateFrom()->getTimestamp());
			} else {
				$s2018++;
				$this->assertEquals($startDate2019->getTimestamp(), $item->getDateFrom()->getTimestamp());
			}
		}
		$this->assertEquals(5, $s2020);
		$this->assertEquals(3, $s2019);
		$this->assertEquals(2, $s2018);
	}

	public function testFindByTeamIdAndSeasonWithTeamIdAndSeason()
	{
		$teamId = uniqid();
		$startDate2021 = (new DateTimeImmutable('2021-02-05'));
		$startDate2020 = (new DateTimeImmutable('2020-02-05'));

		for ($i = 0; $i < 2; $i++) {
			$this->persistTransfer(uniqid(), $startDate2021, $teamId);
		}

		for ($i = 0; $i < 3; $i++) {
			$this->persistTransfer(uniqid(), $startDate2020, $teamId);
		}

		$this->assertCount(5, $this->transferRepository->findAll());
		$result = $this->transferRepository->findAllByTeamIdAndSeason(Transfer::ATTR_TEAM_ID, $teamId, '2020-2021');

		$this->assertCount(2, $result);
		/**
		 * @var Transfer $item
		 */
		foreach ($result as $item) {
			$this->assertEquals($teamId, $item->getTeamId());
			$this->assertEquals("2020-2021", $item->getSeason());
			$this->assertEquals($startDate2021->getTimestamp(), $item->getDateFrom()->getTimestamp());
		}
	}

	public function testFindByTeamIdAndSeasonWithOnLoanTeamWithoutSeason()
	{
		$teamId = uniqid();
		$startDate2021 = (new DateTimeImmutable('2021-02-05'));
		$startDate2020 = (new DateTimeImmutable('2020-02-05'));
		$startDate2019 = (new DateTimeImmutable('2019-02-05'));

		for ($i = 0; $i < 5; $i++) {
			$this->persistTransfer(uniqid(), $startDate2021, uniqid(), $teamId);
		}

		for ($i = 0; $i < 3; $i++) {
			$this->persistTransfer(uniqid(), $startDate2020, uniqid(), $teamId);
		}

		for ($i = 0; $i < 2; $i++) {
			$this->persistTransfer(uniqid(), $startDate2019, uniqid(), $teamId);
		}

		for ($i = 0; $i < 5; $i++) {
			$this->persistTransfer(uniqid(), $startDate2019, $teamId, uniqid());
		}

		$this->assertCount(15, $this->transferRepository->findAll());
		$result = $this->transferRepository->findAllByTeamIdAndSeason(Transfer::ATTR_ON_LOAN_FROM_ID, $teamId);
		$this->assertCount(10, $result);
		$s2020 = $s2019 = $s2018 = 0;
		/**
		 * @var Transfer $item
		 */
		foreach ($result as $item) {
			$this->assertEquals($teamId, $item->getOnLoanFromId());
			$this->assertContains($item->getSeason(), ["2020-2021", "2019-2020", "2018-2019"]);
			if ($item->getSeason() == "2020-2021") {
				$s2020++;
				$this->assertEquals($startDate2021->getTimestamp(), $item->getDateFrom()->getTimestamp());
			} elseif ($item->getSeason() == "2019-2020") {
				$s2019++;
				$this->assertEquals($startDate2020->getTimestamp(), $item->getDateFrom()->getTimestamp());
			} else {
				$s2018++;
				$this->assertEquals($startDate2019->getTimestamp(), $item->getDateFrom()->getTimestamp());
			}
		}
		$this->assertEquals(5, $s2020);
		$this->assertEquals(3, $s2019);
		$this->assertEquals(2, $s2018);
	}

	public function testFindByTeamIdAndSeasonWithOnLoanTeamAndSeason()
	{
		$teamId = uniqid();
		$startDate2021 = (new DateTimeImmutable('2021-02-05'));
		$startDate2020 = (new DateTimeImmutable('2020-02-05'));

		for ($i = 0; $i < 2; $i++) {
			$this->persistTransfer(uniqid(), $startDate2021, uniqid(), $teamId);
		}

		for ($i = 0; $i < 3; $i++) {
			$this->persistTransfer(uniqid(), $startDate2020, uniqid(), $teamId);
		}

		$this->assertCount(5, $this->transferRepository->findAll());
		$result = $this->transferRepository->findAllByTeamIdAndSeason(Transfer::ATTR_ON_LOAN_FROM_ID, $teamId, '2020-2021');

		$this->assertCount(2, $result);
		/**
		 * @var Transfer $item
		 */
		foreach ($result as $item) {
			$this->assertEquals($teamId, $item->getOnLoanFromId());
			$this->assertEquals("2020-2021", $item->getSeason());
			$this->assertEquals($startDate2021->getTimestamp(), $item->getDateFrom()->getTimestamp());
		}
	}

	public function testFindByTeamIdAndSeasonWhenItemNotExists()
	{
		for ($i = 0; $i < 5; $i++) {
			$this->persistTransfer();
		}
		$result = $this->transferRepository->findAllByTeamIdAndSeason(Transfer::ATTR_ON_LOAN_FROM_ID, uniqid(), '2020-2021');
		$this->assertEmpty($result);
	}

	protected function tearDown(): void
	{
		$this->transferRepository->drop();
	}
}