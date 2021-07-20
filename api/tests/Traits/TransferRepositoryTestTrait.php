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
	 * @param string|null $playerId
	 * @param DateTimeImmutable|null $startDate
	 * @param bool $active
	 * @throws \App\Exceptions\ReadModelValidatorException
	 */
	private function createTransferModel(?string $playerId = null, ?DateTimeImmutable $startDate = null, bool $active = false
		, string $toTeam = "1", string $fromTeam = "2")
	{
		$transferModel = (new Transfer())
			->setId($this->faker->uuid)
			->setPlayerId($playerId ?? $this->faker->uuid)
			->setPlayerName($this->faker->name)
			->setPlayerPosition('defender')
			->setFromTeamId($fromTeam)
			->setToTeamId($toTeam)
			->setFromTeamName('Team A')
			->setToTeamName('Team B')
			->setMarketValue(200)
			->setStartDate($startDate)
			->setEndDate(new DateTimeImmutable())
			->setAnnouncedDate(new DateTimeImmutable())
			->setContractDate(new DateTimeImmutable())
			->setType('transferred')
			->setActive($active)
			->setCreatedAt(new DateTime());

		$transferModel->prePersist();
		$this->transferRepository->persist($transferModel);
	}

	private function persistBatchDataForListByTeam(string $fakeTeamId, string $fakeTeamName): void
	{
		$fakePlayerPositions = ['defender', 'forward', 'goalkeeper', 'midfielder'];
		for ($i = 1; $i < 10; $i++) {
			$fakeTransferModel = $this->createTransferModel()
				->setStartDate(new DateTimeImmutable(sprintf( "2020-%d-01", rand( 1, 12 ))))
				->setPlayerPosition($fakePlayerPositions[$this->faker->numberBetween(0, 3)]);
			$fakeTransferModel->prePersist();
			$this->transferRepository->persist($fakeTransferModel);
		}
		for ($i = 1; $i < 10; $i++) {
			$fakeTransferModel = $this->createTransferModel();
			if (in_array($i, [1,2,3,4])) {
				$fakeTransferModel
					->setStartDate(new DateTimeImmutable(sprintf( "2020-%d-01", $this->faker->numberBetween(1, 2))))
					->setPlayerPosition($fakePlayerPositions[$this->faker->numberBetween(0, 3)])
					->setFromTeamId($fakeTeamId)
					->setFromTeamName($fakeTeamName);
			} else {
				$fakeTransferModel
					->setStartDate(new DateTimeImmutable(sprintf( "2020-%d-01", $this->faker->numberBetween(3, 12))))
					->setPlayerPosition($fakePlayerPositions[$this->faker->numberBetween(0, 3)])
					->setToTeamId($fakeTeamId)
					->setToTeamName($fakeTeamName);
			}
			$fakeTransferModel->prePersist();
			$this->transferRepository->persist($fakeTransferModel);
		}
	}

	private function persistBatchDataForListByPlayer(string $fakePlayerId, string $fakePlayerName): void
	{
		for ($i = 0; $i < 5; $i++) {
			$fakeTransferModel = $this->createTransferModel();
			$fakeTransferModel->setPlayerName($fakePlayerName)
				->setStartDate(new DateTimeImmutable( sprintf( "2019-%d-01", rand( 1, 12 ) )))
				->setPlayerId($fakePlayerId);
			$fakeTransferModel->prePersist();
			$this->transferRepository->persist($fakeTransferModel);
		}
	}
}