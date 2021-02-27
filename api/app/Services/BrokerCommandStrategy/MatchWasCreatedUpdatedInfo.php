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
use App\ValueObjects\Broker\CommandQuery\Headers;
use App\ValueObjects\Broker\CommandQuery\Message;
use Psr\Log\LoggerInterface;
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
	private LoggerInterface $logger;
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
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		TeamsMatchRepository $teamsMatchRepository,
		TeamsMatchService $teamsMatchService,
		TeamsMatchCacheServiceInterface $teamsMatchCacheService,
		BrokerMessageCacheServiceInterface $brokerMessageCacheService,
		HubInterface $sentryHub,
		SerializerInterface $serializer,
		LoggerInterface $logger
	) {
		$this->teamsMatchRepository = $teamsMatchRepository;
		$this->brokerMessageCacheService = $brokerMessageCacheService;
		$this->sentryHub = $sentryHub;
		$this->serializer = $serializer;
		$this->logger = $logger;
		$this->teamsMatchCacheService = $teamsMatchCacheService;
		$this->teamsMatchService = $teamsMatchService;
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
		$this->logger->alert(
			sprintf(
				"Answer %s by %s will handle by %s.",
				MatchWasCreatedProjectorListener::BROKER_EVENT_KEY,
				$commandQuery->getHeaders()->getSource(),
				__CLASS__
			),
			$this->serializer->normalize($commandQuery, 'array')
		);
		$this->logger->alert(
			sprintf("%s handler in progress.", MatchWasCreatedProjectorListener::BROKER_EVENT_KEY),
			$this->serializer->normalize($commandQuery, 'array')
		);
		if (empty($commandQuery->getBody())) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s.",
					MatchWasCreatedProjectorListener::BROKER_EVENT_KEY,
					'Data not found.'
				),
				$this->serializer->normalize($commandQuery, 'array')
			);
			return;
		}
		[$matchId, $homeTeamId, $awayTeamId] = explode('#', $commandQuery->getHeaders()->getId());
		$this->teamsMatchCacheService->forget('teams_match*');
		$this->updateTeamsMatch($matchId, $homeTeamId, $commandQuery->getBody()['competitionName']);
		$this->updateTeamsMatch($matchId, $awayTeamId, $commandQuery->getBody()['competitionName']);
		/**
		 * Put competitionName in cache.
		 */
		$this->brokerMessageCacheService->putCompetitionName([
			'id' => $commandQuery->getBody()['id'],
			'name' => $commandQuery->getBody()['competitionName']
		]);
		$this->logger->alert(
			sprintf("%s handler completed successfully.", MatchWasCreatedProjectorListener::BROKER_EVENT_KEY),
			$this->serializer->normalize($commandQuery, 'array')
		);
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
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s.",
					MatchWasCreatedProjectorListener::BROKER_EVENT_KEY,
					'Failed to persist teamsMatch.'
				),
				$this->serializer->normalize($teamsMatchItem, 'array')
			);
			$this->sentryHub->captureException($exception);
		}
		/**	create cache */
		try {
			$this->teamsMatchService->getTeamsMatchInfo($teamId);
		} catch (\Exception $exception) {
		}
	}
}