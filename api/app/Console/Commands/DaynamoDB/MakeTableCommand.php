<?php


namespace App\Console\Commands\DaynamoDB;


use App\Models\Repositories\TeamRepository;
use App\Models\Repositories\TeamsMatchRepository;
use App\Models\Repositories\TransferRepository;
use App\Models\Repositories\TrophyRepository;
use Illuminate\Console\Command;


/**
 * Class MakeTableCommand
 * @package App\Console\Commands\DaynamoDB
 */
class MakeTableCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'make:table';

    /**
     * @var string
     */
    protected $description = 'Make table in DynamoDB.';

	private TransferRepository $transferRepository;
	private TrophyRepository $trophyRepository;
	private TeamRepository $teamRepository;
	private TeamsMatchRepository $teamsMatchRepository;

	/**
	 * MakeTableCommand constructor.
	 * @param TransferRepository $transferRepository
	 * @param TrophyRepository $trophyRepository
	 * @param TeamRepository $teamRepository
	 * @param TeamsMatchRepository $teamsMatchRepository
	 */
    public function __construct(
    	TransferRepository $transferRepository,
		TrophyRepository $trophyRepository,
		TeamRepository $teamRepository,
		TeamsMatchRepository $teamsMatchRepository
	) {
        parent::__construct();
		$this->transferRepository = $transferRepository;
		$this->trophyRepository = $trophyRepository;
		$this->teamRepository = $teamRepository;
		$this->teamsMatchRepository = $teamsMatchRepository;
	}

    /**
     * @throws \App\Exceptions\DynamoDB\DynamoDBRepositoryException
     */
    public function handle()
    {
		if (! in_array(TransferRepository::getTableName(), $this->transferRepository->getDynamoDbClient()->listTables()->toArray()['TableNames'])) {
			$this->transferRepository->createTable();
		} else {
			throw new \Exception(sprintf('Exist %s Table', TransferRepository::getTableName()));
		}

		if (! in_array(TrophyRepository::getTableName(), $this->trophyRepository->getDynamoDbClient()->listTables()->toArray()['TableNames'])) {
			$this->trophyRepository->createTable();
		} else {
			throw new \Exception(sprintf('Exist %s Table', TrophyRepository::getTableName()));
		}

		if (! in_array(TeamRepository::getTableName(), $this->teamRepository->getDynamoDbClient()->listTables()->toArray()['TableNames'])) {
			$this->teamRepository->createTable();
		} else {
			throw new \Exception(sprintf('Exist %s Table', TeamRepository::getTableName()));
		}

		if (! in_array(TeamsMatchRepository::getTableName(), $this->teamsMatchRepository->getDynamoDbClient()->listTables()->toArray()['TableNames'])) {
			$this->teamsMatchRepository->createTable();
		} else {
			throw new \Exception(sprintf('Exist %s Table', TeamsMatchRepository::getTableName()));
		}
    }
}
