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
	private function createTransferModel(?string $personId = null, ?DateTimeImmutable $dateFrom = null, ?string $teamId = null,?string $onLoanFromId = null)
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

	private function persistBatchDataForListByTeam(string $fakeTeamId, string $fakeTeamName): void
	{
		$fakePersonPositions = ['defender', 'forward', 'goalkeeper', 'midfielder'];
		for ($i = 1; $i < 10; $i++) {
			$fakeTransferModel = $this->createTransferModel()
				->setStartDate(new DateTimeImmutable(sprintf( "2020-%d-01", rand( 1, 12 ))))
				->setPersonPosition($fakePersonPositions[$this->faker->numberBetween(0, 3)]);
			$fakeTransferModel->prePersist();
			$this->transferRepository->persist($fakeTransferModel);
		}
		for ($i = 1; $i < 10; $i++) {
			$fakeTransferModel = $this->createTransferModel();
			if (in_array($i, [1,2,3,4])) {
				$fakeTransferModel
					->setStartDate(new DateTimeImmutable(sprintf( "2020-%d-01", $this->faker->numberBetween(1, 2))))
					->setPersonPosition($fakePersonPositions[$this->faker->numberBetween(0, 3)])
					->setFromTeamId($fakeTeamId)
					->setFromTeamName($fakeTeamName);
			} else {
				$fakeTransferModel
					->setStartDate(new DateTimeImmutable(sprintf( "2020-%d-01", $this->faker->numberBetween(3, 12))))
					->setPersonPosition($fakePersonPositions[$this->faker->numberBetween(0, 3)])
					->setToTeamId($fakeTeamId)
					->setToTeamName($fakeTeamName);
			}
			$fakeTransferModel->prePersist();
			$this->transferRepository->persist($fakeTransferModel);
		}
	}

	private function persistBatchDataForListByPerson(string $fakePersonId, string $fakePersonName): void
	{
		for ($i = 0; $i < 5; $i++) {
			$fakeTransferModel = $this->createTransferModel();
			$fakeTransferModel->setPersonName($fakePersonName)
				->setStartDate(new DateTimeImmutable( sprintf( "2019-%d-01", rand( 1, 12 ) )))
				->setPersonId($fakePersonId);
			$fakeTransferModel->prePersist();
			$this->transferRepository->persist($fakeTransferModel);
		}
	}
}