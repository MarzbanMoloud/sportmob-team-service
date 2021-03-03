<?php


namespace App\Services\BrokerCommandStrategy;


use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Http\Services\Trophy\TrophyService;
use App\Listeners\Projection\TrophyProjectorListener;
use App\Listeners\Traits\TeamBecameWinnerNotificationTrait;
use App\Models\ReadModels\Trophy;
use App\Models\Repositories\TrophyRepository;
use App\Services\BrokerCommandStrategy\Interfaces\BrokerCommandEventInterface;
use App\Services\BrokerInterface;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
use App\Services\Cache\Interfaces\TrophyCacheServiceInterface;
use App\ValueObjects\Broker\CommandQuery\Headers;
use App\ValueObjects\Broker\CommandQuery\Message;
use Psr\Log\LoggerInterface;
use Sentry\State\HubInterface;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class TrophyUpdateInfo
 * @package App\Services\BrokerCommandStrategy
 */
class TrophyUpdateInfo implements BrokerCommandEventInterface
{
	use TeamBecameWinnerNotificationTrait;

	private BrokerMessageCacheServiceInterface $brokerMessageCacheService;
	private HubInterface $sentryHub;
	private TrophyRepository $trophyRepository;
	private SerializerInterface $serializer;
	private LoggerInterface $logger;
	private TrophyService $trophyService;
	private TrophyCacheServiceInterface $trophyCacheService;
	private BrokerInterface $broker;

	/**
	 * TrophyUpdateInfo constructor.
	 * @param BrokerMessageCacheServiceInterface $brokerMessageCacheService
	 * @param HubInterface $sentryHub
	 * @param TrophyService $trophyService
	 * @param TrophyCacheServiceInterface $trophyCacheService
	 * @param TrophyRepository $trophyRepository
	 * @param SerializerInterface $serializer
	 * @param LoggerInterface $logger
	 * @param BrokerInterface $broker
	 */
	public function __construct(
		BrokerMessageCacheServiceInterface $brokerMessageCacheService,
		HubInterface $sentryHub,
		TrophyService $trophyService,
		TrophyCacheServiceInterface $trophyCacheService,
		TrophyRepository $trophyRepository,
		SerializerInterface $serializer,
		LoggerInterface $logger,
		BrokerInterface $broker
	) {
		$this->brokerMessageCacheService = $brokerMessageCacheService;
		$this->sentryHub = $sentryHub;
		$this->trophyRepository = $trophyRepository;
		$this->serializer = $serializer;
		$this->logger = $logger;
		$this->trophyService = $trophyService;
		$this->trophyCacheService = $trophyCacheService;
		$this->broker = $broker;
	}

	/**
	 * @param Headers $headers
	 * @return bool
	 */
	public function support(Headers $headers): bool
	{
		return
			($headers->getDestination() == config('broker.services.team_name')) &&
			($headers->getKey() == TrophyProjectorListener::BROKER_EVENT_KEY);
	}

	/**
	 * @param Message $commandQuery
	 */
	public function handle(Message $commandQuery): void
	{
		$this->logger->alert(
			sprintf(
				"Answer %s by %s will handle by %s.",
				TrophyProjectorListener::BROKER_EVENT_KEY,
				$commandQuery->getHeaders()->getSource(),
				__CLASS__
			),
			$this->serializer->normalize($commandQuery, 'array')
		);
		$this->logger->alert(
			sprintf("%s handler in progress.", TrophyProjectorListener::BROKER_EVENT_KEY),
			$this->serializer->normalize($commandQuery, 'array')
		);
		if (empty($commandQuery->getBody())) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s.",
					TrophyProjectorListener::BROKER_EVENT_KEY,
					'Data not found.'
				),
				$this->serializer->normalize($commandQuery, 'array')
			);
			return;
		}
		[$competitionId, $tournamentId, $teamId] = explode('#', $commandQuery->getHeaders()->getId());
		$sortKey = sprintf("%s#%s", $tournamentId, $teamId);
		/** @var Trophy $trophy */
		$trophy = $this->trophyRepository->find(['competitionId' => $competitionId, 'sortKey' => $sortKey]);
		$trophy->setTournamentSeason($commandQuery->getBody()['season'])
			->setCompetitionName($commandQuery->getBody()['competitionName']);
		try {
			$this->trophyRepository->persist($trophy);
		} catch (DynamoDBRepositoryException $exception) {
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s.",
					TrophyProjectorListener::BROKER_EVENT_KEY,
					'Failed to persist trophy.'
				),
				$this->serializer->normalize($trophy, 'array')
			);
			$this->sentryHub->captureException($exception);
		}
		try {
			$this->trophyCacheService->forget('trophies_by_*');//per teamId and competitionId.
			$this->trophyService->getTrophiesByTeam($teamId);
		} catch (\Exception $e) {
		}
		$data = $commandQuery->getBody();
		unset($data['entity']);
		$this->brokerMessageCacheService->putTournamentInfo($data);
		if (($trophy->getPosition() == Trophy::POSITION_WINNER) && (strpos($trophy->getTournamentSeason(), date('Y')) != false)) {
			$this->sendNotification($trophy, TrophyProjectorListener::BROKER_NOTIFICATION_KEY);
		}
		$this->logger->alert(
			sprintf("%s handler completed successfully.", TrophyProjectorListener::BROKER_EVENT_KEY),
			$this->serializer->normalize($trophy, 'array')
		);
	}
}