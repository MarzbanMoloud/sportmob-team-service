<?php


namespace App\Services\BrokerCommandStrategy;


use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Listeners\Projection\MatchWasCreatedProjectorListener;
use App\Models\ReadModels\TeamsMatch;
use App\Models\Repositories\TeamsMatchRepository;
use App\Services\BrokerCommandStrategy\Interfaces\BrokerCommandEventInterface;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
use App\ValueObjects\Broker\CommandQuery\Headers;
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
	 * @param Headers $headers
	 * @return bool
	 */
	public function support(Headers $headers): bool
	{
		return
			($headers->getDestination() == config('broker.services.team_name')) &&
			($headers->getKey() == MatchWasCreatedProjectorListener::BROKER_EVENT_KEY);
	}

	/**
	 * @param Message $commandQuery
	 */
	public function handle(Message $commandQuery): void
	{
		if (empty($commandQuery->getBody())) {
			return;
		}
		[$matchId, $homeTeamId, $awayTeamId] = explode('#', $commandQuery->getHeaders()->getId());
		$this->updateTeamsMatch($matchId, $homeTeamId, $commandQuery->getBody()['competitionName']);
		$this->updateTeamsMatch($matchId, $awayTeamId, $commandQuery->getBody()['competitionName']);
		/**
		 * Put competitionName in cache.
		 */
		$this->brokerMessageCacheService->putCompetitionName([
			'id' => $commandQuery->getBody()['id'],
			'name' => $commandQuery->getBody()['competitionName']
		]);
	}

	/**
	 * @param string $matchId
	 * @param string $teamId
	 * @param string $competitionName
	 */
	private function updateTeamsMatch(string $matchId, string $teamId, string $competitionName): void
	{
		$teamsMatchItem = $this->teamsMatchRepository->find([
			'matchId' => $matchId,
			'teamId' => $teamId
		]);
		if (!$teamsMatchItem) {
			return;
		}
		/** @var TeamsMatch $teamsMatchItem */
		$teamsMatchItem->setCompetitionName($competitionName);
		try {
			$this->teamsMatchRepository->persist($teamsMatchItem);
		} catch (DynamoDBRepositoryException $exception) {
			$this->sentryHub->captureException($exception);
		}
	}
}