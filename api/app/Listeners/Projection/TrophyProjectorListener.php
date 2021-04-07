<?php


namespace App\Listeners\Projection;


use App\Events\Projection\TrophyProjectorEvent;
use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Exceptions\Projection\ProjectionException;
use App\Listeners\Traits\TeamBecameWinnerNotificationTrait;
use App\Models\ReadModels\Trophy;
use App\Models\Repositories\TrophyRepository;
use App\Services\BrokerInterface;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
use App\Services\Logger\Event;
use App\ValueObjects\Broker\CommandQuery\Headers;
use App\ValueObjects\Broker\CommandQuery\Message;
use Carbon\Carbon;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class TrophyProjectorListener
 * @package App\Listeners\Projection
 */
class TrophyProjectorListener
{
	use TeamBecameWinnerNotificationTrait;

	const BROKER_EVENT_KEY = 'TrophyUpdateInfo';
	const BROKER_NOTIFICATION_KEY = 'team-champion';


	private BrokerInterface $broker;
	private SerializerInterface $serializer;
	private BrokerMessageCacheServiceInterface $brokerMessageCacheService;
	private TrophyRepository $trophyRepository;

	/**
	 * TrophyProjectorListener constructor.
	 * @param BrokerInterface $broker
	 * @param BrokerMessageCacheServiceInterface $brokerMessageCacheService
	 * @param TrophyRepository $trophyRepository
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		BrokerInterface $broker,
		BrokerMessageCacheServiceInterface $brokerMessageCacheService,
		TrophyRepository $trophyRepository,
		SerializerInterface $serializer
	) {
		$this->serializer = $serializer;
		$this->broker = $broker;
		$this->brokerMessageCacheService = $brokerMessageCacheService;
		$this->trophyRepository = $trophyRepository;
		$this->serializer = $serializer;
	}

	/**
	 * @param TrophyProjectorEvent $event
	 * @throws ProjectionException
	 */
	public function handle(TrophyProjectorEvent $event)
	{
		if (!$this->brokerMessageCacheService->hasTournamentInfo($event->trophy->getTournamentId())) {
			$message = (new Message())
				->setHeaders(
					(new Headers())
						->setKey(self::BROKER_EVENT_KEY)
						->setId(sprintf('%s#%s#%s', $event->trophy->getCompetitionId(),
							$event->trophy->getTournamentId(), $event->trophy->getTeamId()))
						->setDestination(config('broker.services.competition_name'))
						->setSource(config('broker.services.team_name'))
						->setDate(Carbon::now()->format('c'))
				)->setBody([
					'entity' => config('broker.services.tournament_name'),
					'id' => $event->trophy->getTournamentId()
				]);
			$this->broker->flushMessages()->addMessage(
				self::BROKER_EVENT_KEY,
				$this->serializer->serialize($message, 'json')
			)->produceMessage(config('broker.topics.question_competition'));
			Event::needToAsk($message, $event->eventName, self::BROKER_EVENT_KEY, config('broker.services.competition_name'));
			return;
		}
		$tournamentInfo = $this->brokerMessageCacheService->getTournamentInfo($event->trophy->getTournamentId());
		$event->trophy
			->setCompetitionName($tournamentInfo['competitionName'])
			->setTournamentSeason($tournamentInfo['season']);
		try {
			$this->trophyRepository->persist($event->trophy);
		} catch (DynamoDBRepositoryException $exception) {
			$message = 'Failed to update trophy.';
			$trophyArray = $this->serializer->normalize($event->trophy, 'array');
			$temp['_teamName'] = $trophyArray['teamName'];
			unset($trophyArray['teamName']);
			Event::failed($trophyArray, $event->eventName, $message);
			throw new ProjectionException($message, $exception->getCode(), $exception);
		}

		if (($event->trophy->getPosition() == Trophy::POSITION_WINNER) && (strpos($event->trophy->getTournamentSeason(), date('Y')) != false)){
			$this->sendNotification($event->trophy,self::BROKER_NOTIFICATION_KEY);
		}
	}
}