<?php


namespace Tests\Traits;


use App\Models\ReadModels\Transfer;
use App\Models\Repositories\TransferRepository;
use DateTime;
use DateTimeImmutable;


/**
 * Trait TransferRepositoryTestTrait
 * @package Tests\Traits
 */
trait TransferRepositoryTestTrait
{
	public function createTransferTable(): void
	{
		if (in_array(TransferRepository::getTableName(), $this->transferRepository->getDynamoDbClient()->listTables()->toArray()['TableNames'])) {
			$this->transferRepository->drop();
		}
		$this->transferRepository->createTable();
	}

	/**
	 * @param string|null $personId
	 * @param DateTimeImmutable|null $dateFrom
	 * @param string|null $teamId
	 * @param string|null $onLoanFromId
	 */
	private function persistTransfer(?string $personId = null, ?DateTimeImmutable $dateFrom = null, ?string $teamId = null,?string $onLoanFromId = null)
	{
		$transferModel = (new Transfer())
			->setId($this->faker->uuid)
			->setPersonId($personId ?? $this->faker->uuid)
			->setPersonName($this->faker->name)
			->setPersonType('player')
			->setTeamId($teamId ?? $this->faker->uuid)
			->setTeamName($this->faker->city)
			->setOnLoanFromId($onLoanFromId ?? $this->faker->uuid)
			->setOnLoanFromName($onLoanFromId ? $this->faker->city : null)
			->setDateFrom($dateFrom ?? Transfer::getDateTimeImmutable())
			->setDateTo(new DateTimeImmutable())
			->setMarketValue(200)
			->setAnnouncedDate(new DateTimeImmutable())
			->setContractDate(new DateTimeImmutable())
			->setCreatedAt(new DateTime());

		$transferModel->prePersist();
		$this->transferRepository->persist($transferModel);
	}

	/**
	 * @param string|null $personId
	 * @param DateTimeImmutable|null $dateFrom
	 * @param DateTimeImmutable|null $dateTo
	 * @param string|null $onLoanFromId
	 * @return Transfer
	 */
	private function createTransferModel(?string $personId = null, ?DateTimeImmutable $dateFrom = null, ?DateTimeImmutable $dateTo = null, ?string $onLoanFromId = null): Transfer
	{
		return (new Transfer())
			->setId($this->faker->uuid)
			->setPersonId($personId ?? $this->faker->uuid)
			->setPersonName($this->faker->name)
			->setPersonType('player')
			->setTeamId($teamId ?? $this->faker->uuid)
			->setTeamName($this->faker->city)
			->setOnLoanFromId($onLoanFromId ?? Transfer::DEFAULT_VALUE)
			->setOnLoanFromName($onLoanFromId ? $this->faker->city : null)
			->setDateFrom($dateFrom ?? Transfer::getDateTimeImmutable())
			->setDateTo($dateTo ?? null)
			->setMarketValue(200)
			->setAnnouncedDate(new DateTimeImmutable())
			->setContractDate(new DateTimeImmutable())
			->setCreatedAt(new DateTime());
	}

	/**
	 * @param string $personId
	 * @throws \Exception
	 */
	private function persistBatchDataForListByPerson(string $personId): void
	{
		/** Transfer, dateFrom and dateTo fields are null. */
		$fakeTransferModel = $this->createTransferModel($personId);
		$fakeTransferModel->prePersist();
		$this->transferRepository->persist($fakeTransferModel);

		/** Transfer, dateFrom and dateTo fields are not null and without loan. */
		$dates = [
			['from' => '1999-01-01', 'to' => '2001-01-01'],
			['from' => '2001-01-01', 'to' => '2004-01-01'],
			['from' => '2004-09-12', 'to' => '2006-08-08'],
			['from' => '2006-08-10', 'to' => '2009-07-27'],
			['from' => '2009-07-27', 'to' => '2011-06-30'],
		];
		for ($i = 0; $i < 5; $i++) {
			$fakeTransferModel = $this->createTransferModel($personId, new DateTimeImmutable($dates[$i]['from']), new DateTimeImmutable($dates[$i]['to']));
			$fakeTransferModel->prePersist();
			$this->transferRepository->persist($fakeTransferModel);
		}

		/** Transfer, dateFrom and dateTo fields are not null and with loan. */
		$fakeTransferModel = $this->createTransferModel($personId, new DateTimeImmutable('2010-08-28'), new DateTimeImmutable('2011-06-30'), $this->faker->uuid);
		$fakeTransferModel->prePersist();
		$this->transferRepository->persist($fakeTransferModel);

		/** Transfer, dateFrom and dateTo fields are not null and without loan. */
		$dates = [
			['from' => '2011-07-01', 'to' => '2012-07-17'],
			['from' => '2012-07-17', 'to' => '2016-06-30'],
			['from' => '2016-07-01', 'to' => '2017-07-01'],
			['from' => '2017-08-24', 'to' => '2018-03-22'],
			['from' => '2018-03-23', 'to' => '2019-10-25'],
		];
		for ($i = 0; $i < 5; $i++) {
			$fakeTransferModel = $this->createTransferModel($personId, new DateTimeImmutable($dates[$i]['from']), new DateTimeImmutable($dates[$i]['to']));
			$fakeTransferModel->prePersist();
			$this->transferRepository->persist($fakeTransferModel);
		}

		/** Transfer, dateFrom is fill and dateTo is null and without loan. */
		$fakeTransferModel = $this->createTransferModel($personId, new DateTimeImmutable('2019-12-27'));
		$fakeTransferModel->prePersist();
		$this->transferRepository->persist($fakeTransferModel);
	}
}