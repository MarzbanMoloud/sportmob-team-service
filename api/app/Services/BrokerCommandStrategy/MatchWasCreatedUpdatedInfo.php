<?php


namespace App\Services\BrokerCommandStrategy;


use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Http\Services\TeamsMatch\TeamsMatchService;
use App\Listeners\Projection\MatchWasCreatedProjectorListener;
use App\Models\ReadModels\TeamsMatch;
use App\Models\Repositories\TeamsMatchRepository;
use App\Services\BrokerCommandStrategy\Interfaces\BrokerCommandEventInterface;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
use App\Services\Cache\Interfaces\TeamsMatchCacheServiceInterface;
use App\Services\Logger\Answer;
use App\ValueObjects\Broker\CommandQuery\Message;
use Sentry\State\HubInterface;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class MatchWasCreatedUpdatedInfo
 * @package App\Services\BrokerCommandStrategy
 */
class MatchWasCreatedUpdatedInfo implements BrokerCommandEventInterface
{
	private TeamsMatchRepository $teamsMatchRepository;
	private BrokerMessageCacheServiceInterface $brokerMessageCacheService;
	private HubInterface $sentryHub;
	private SerializerInterface $serializer;
	private TeamsMatchCacheServiceInterface $teamsMatchCacheService;
	private TeamsMatchService $teamsMatchService;

	/**
	 * MatchWasCreatedUpdatedInfo constructor.
	 * @param TeamsMatchRepository $teamsMatchRepository
	 * @param TeamsMatchService $teamsMatchService
	 * @param TeamsMatchCacheServiceInterface $teamsMatchCacheService
	 * @param BrokerMessageCacheServiceInterface $brokerMessageCacheService
	 * @param HubInterface $sentryHub
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		TeamsMatchRepository $teamsMatchRepository,
		TeamsMatchService $teamsMatchService,
		TeamsMatchCacheServiceInterface $teamsMatchCacheService,
		BrokerMessageCacheServiceInterface $brokerMessageCacheService,
		HubInterface $sentryHub,
		SerializerInterface $serializer
	) {
		$this->teamsMatchRepository = $teamsMatchRepository;
		$this->brokerMessageCacheService = $brokerMessageCacheService;
		$this->sentryHub = $sentryHub;
		$this->serializer = $serializer;
		$this->teamsMatchCacheService = $teamsMatchCacheService;
		$this->teamsMatchService = $teamsMatchService;
	}

	/**
	 * @param Message $commandQuery
	 */
	public function handle(Message $commandQuery): void
	{
		Answer::handled($commandQuery, MatchWasCreatedProjectorListener::BROKER_EVENT_KEY, $commandQuery->getHeaders()->getSource(), __CLASS__);
		Answer::processing($commandQuery, MatchWasCreatedProjectorListener::BROKER_EVENT_KEY);
		if (empty($commandQuery->getBody())) {
			Answer::failed($commandQuery, MatchWasCreatedProjectorListener::BROKER_EVENT_KEY, 'Data not found.');
			return;
		}
		[$matchId, $homeTeamId, $awayTeamId] = explode('#', $commandQuery->getHeaders()->getId());
		$this->teamsMatchCacheService->forget('teams_match*');
		$this->updateTeamsMatch($matchId, $homeTeamId, $commandQuery->getBody()['name']);
		$this->updateTeamsMatch($matchId, $awayTeamId, $commandQuery->getBody()['name']);
		/**
		 * Put competitionName in cache.
		 */
		$this->brokerMessageCacheService->putCompetitionName([
			'id' => $commandQuery->getBody()['id'],
			'name' => $commandQuery->getBody()['name']
		]);
		Answer::succeeded($commandQuery, MatchWasCreatedProjectorListener::BROKER_EVENT_KEY);
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
			Answer::failed($teamsMatchItem, MatchWasCreatedProjectorListener::BROKER_EVENT_KEY, 'Failed to persist teamsMatch.');
			$this->sentryHub->captureException($exception);
		}
		/**	create cache */
		try {
			$this->teamsMatchService->getTeamsMatchInfo($teamId);
		} catch (\Exception $exception) {
		}
	}
}