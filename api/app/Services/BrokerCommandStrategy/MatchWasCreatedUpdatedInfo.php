<?php


namespace App\Services\BrokerCommandStrategy;


use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Listeners\Projection\MatchWasCreatedProjectorListener;
use App\Models\ReadModels\TeamsMatch;
use App\Models\Repositories\TeamsMatchRepository;
use App\Services\BrokerCommandStrategy\Interfaces\BrokerCommandEventInterface;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
use App\ValueObjects\Broker\CommandQuery\Message;
use Sentry\State\HubInterface;


/**
 * Class MatchWasCreatedUpdatedInfo
 * @package App\Services\BrokerCommandStrategy
 */
class MatchWasCreatedUpdatedInfo implements BrokerCommandEventInterface
{
	private TeamsMatchRepository $teamsMatchRepository;
	private BrokerMessageCacheServiceInterface $brokerMessageCacheService;
	private HubInterface $sentryHub;

	/**
	 * MatchWasCreatedUpdatedInfo constructor.
	 * @param TeamsMatchRepository $teamsMatchRepository
	 * @param BrokerMessageCacheServiceInterface $brokerMessageCacheService
	 * @param HubInterface $sentryHub
	 */
	public function __construct(
		TeamsMatchRepository $teamsMatchRepository,
		BrokerMessageCacheServiceInterface $brokerMessageCacheService,
		HubInterface $sentryHub
	) {
		$this->teamsMatchRepository = $teamsMatchRepository;
		$this->brokerMessageCacheService = $brokerMessageCacheService;
		$this->sentryHub = $sentryHub;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function support(string $key): bool
	{
		return $key == MatchWasCreatedProjectorListener::BROKER_EVENT_KEY;
	}

	/**
	 * @param Message $commandQuery
	 */
	public function handle(Message $commandQuery): void
	{
		if (empty($commandQuery->getBody())) {
			return;
		}
		$competitionId = $commandQuery->getHeaders()->getId();
		$teamsMatchItems = $this->teamsMatchRepository->findTeamsMatchByCompetitionId($competitionId);
		foreach ($teamsMatchItems as $teamsMatch) {
			/**
			 * @var TeamsMatch $teamsMatch
			 */
			$teamsMatch->setCompetitionName($commandQuery->getBody()['competitionName']);
			try {
				$this->teamsMatchRepository->persist($teamsMatch);
			} catch (DynamoDBRepositoryException $exception) {
				$this->sentryHub->captureException($exception);
			}
		}
		/**
		 * Put competitionName in cache.
		 */
		$this->brokerMessageCacheService->putCompetitionName([
			'id' => $competitionId,
			'name' => $commandQuery->getBody()['competitionName']
		]);
	}
}