<?php


namespace Tests\Traits;


use App\Models\ReadModels\Transfer;
use App\Models\Repositories\TransferRepository;
use App\Services\EventStrategy\MembershipWasUpdated;
use App\ValueObjects\Broker\Mediator\Message;
use Carbon\Carbon;
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
			->setToTeamId($teamId ?? $this->faker->uuid)
			->setToTeamName($this->faker->city)
			->setFromTeamId($onLoanFromId ?? $this->faker->uuid)
			->setFromTeamName($onLoanFromId ? $this->faker->city : null)
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
	 * @param string $teamId
	 * @param string $onLoanFromId
	 * @param string $dateFrom
	 * @return Transfer
	 */
	private function createTransferModel(string $teamId, string $dateFrom, ?string $onLoanFromId = null): Transfer
	{
		return (new Transfer())
			->setId($this->faker->uuid)
			->setPersonId($this->faker->uuid)
			->setPersonName($this->faker->name)
			->setPersonType('player')
			->setToTeamId($teamId ?? $this->faker->uuid)
			->setToTeamName($this->faker->city)
			->setFromTeamId($onLoanFromId ?? Transfer::DEFAULT_VALUE)
			->setFromTeamName($this->faker->city)
			->setDateFrom(new DateTimeImmutable($dateFrom))
			->setDateTo(new DateTimeImmutable('2020-01-20'))
			->setMarketValue(200)
			->setAnnouncedDate(new DateTimeImmutable())
			->setContractDate(new DateTimeImmutable())
			->setType(Transfer::TRANSFER_TYPE_TRANSFERRED)
			->setCreatedAt(new DateTime());
	}

	/**
	 * @param string $personId
	 * @throws \Exception
	 */
	private function persistBatchDataForPerson(string $personId): void
	{
		$message = sprintf('
		{
			"headers":{
                "event": "%s",
                "priority": "1",
                "date": "%s",
                "id": "1"
            },
			"body":{
				"identifiers": {
					"person": "%s"
				 },
				"metadata": {
					"type": "player",
					"membership":[
						{
							"id":"10347",
							"teamId":"10237",
							"teamType":"club",
							"dateFrom":"1999-01-01",
							"dateTo":"2001-01-01",
							"onLoanFrom":null
						},
						{
							"id":"10348",
							"teamId":"8593",
							"teamType":"club",
							"dateFrom":"2001-01-01",
							"dateTo":"2004-01-01",
							"onLoanFrom":null
						},
						{
							"id":"10346",
							"teamId":"9885",
							"teamType":"club",
							"dateFrom":"2004-09-12",
							"dateTo":"2006-08-08",
							"onLoanFrom":null
						},
						{
							"id":"10349",
							"teamId":"8636",
							"teamType":"club",
							"dateFrom":"2006-08-10",
							"dateTo":"2009-07-27",
							"onLoanFrom":null
						},
						{
							"id":"550828",
							"teamId":"8634",
							"teamType":"club",
							"dateFrom":"2009-07-27",
							"dateTo":"2011-06-30",
							"onLoanFrom":null
						},
						{
							"id":"628580",
							"teamId":"8564",
							"teamType":"club",
							"dateFrom":"2010-08-28",
							"dateTo":"2011-06-30",
							"onLoanFrom":"8634"
						},
						{
							"id":"817346",
							"teamId":"8564",
							"teamType":"club",
							"dateFrom":"2011-07-01",
							"dateTo":"2012-07-17",
							"onLoanFrom":null
						},
						{
							"id":"1003995",
							"teamId":"9847",
							"teamType":"club",
							"dateFrom":"2012-07-17",
							"dateTo":"2016-06-30",
							"onLoanFrom":null
						},
						{
							"id":"2137930",
							"teamId":"10260",
							"teamType":"club",
							"dateFrom":"2016-07-01",
							"dateTo":"2017-07-01",
							"onLoanFrom":null
						},
						{
							"id":"2513463",
							"teamId":"10260",
							"teamType":"club",
							"dateFrom":"2017-08-24",
							"dateTo":"2018-03-22",
							"onLoanFrom":null
						},
						{
							"id":"2706023",
							"teamId":"6637",
							"teamType":"club",
							"dateFrom":"2018-03-23",
							"dateTo":"2019-10-25",
							"onLoanFrom":null
						},
						{
							"id":"3241141",
							"teamId":"8564",
							"teamType":"club",
							"dateFrom":"2019-12-27",
							"dateTo":null,
							"onLoanFrom":null
						}
					]
				}
			}
		}',
			config('mediator-event.events.membership_was_updated'),
			Carbon::now()->format('c'),
			$personId);

		/**
		 * @var Message $message
		 */
		$message = $this->serializer->deserialize($message, Message::class, 'json');

		/** Persist team items. */
		foreach ([10237, 8593, 9885, 8636, 8634, 8564, 9847, 10260, 6637] as $teamId) {
			$fakeTeamToModel = $this->createTeamModel()
				->setId($teamId);
			$this->teamRepository->persist($fakeTeamToModel);
		}

		/** Handle event. */
		app(MembershipWasUpdated::class)->handle($message);
	}

	private function persistBatchDataForTeam(string $teamId): void
	{
		$dateFromItems = [
			'2015-04-20',
			'2015-04-20',
			'2016-04-20',
			'2017-04-20',
			'2017-04-20',
			'2018-04-20',
			'2019-04-20',
			'2020-04-20',
			'2021-04-20',
			'2022-04-20',
		];
		for ($i = 0; $i < 10; $i++) {
			$transferModel = $this->createTransferModel($teamId , $dateFromItems[$i]);
			if (in_array($i, [3,5,7])) {
				$transferModel = $this->createTransferModel($this->faker->uuid, $dateFromItems[$i], $teamId);
			}
			$transferModel->prePersist();
			$this->transferRepository->persist($transferModel);
		}
	}
}