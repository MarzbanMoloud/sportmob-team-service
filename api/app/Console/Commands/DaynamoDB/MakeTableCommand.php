<?php


namespace App\Console\Commands\DaynamoDB;


use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
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
	 * @throws \Exception
	 */
	public function handle()
    {
    	$repositories = [
			'transferRepository' => TransferRepository::class,
			'trophyRepository' => TrophyRepository::class,
			'teamRepository' => TeamRepository::class,
			'teamsMatchRepository' => TeamsMatchRepository::class
		];
    	foreach ($repositories as $repository => $repositoryClass) {
			try {
				if (!in_array($repositoryClass::getTableName(),
					$this->$repository->getDynamoDbClient()->listTables()->toArray()['TableNames'])) {
					$this->$repository->createTable();
				}
			} catch (\Exception $e) {
			}
		}
    }
}
