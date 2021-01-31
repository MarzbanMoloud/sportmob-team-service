<?php


namespace App\Listeners\Projection;


use App\Events\Projection\TrophyProjectorEvent;
use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Exceptions\Projection\ProjectionException;
use App\Models\Repositories\TrophyRepository;
use App\Services\BrokerInterface;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
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
	const BROKER_EVENT_KEY = 'TrophyUpdateInfo';

	private BrokerInterface $broker;
	private SerializerInterface $serializer;
	private BrokerMessageCacheServiceInterface $brokerMessageCacheService;
	private TrophyRepository $trophyRepository;

	/**
	 * TrophyProjectorListener constructor.
	 * @param BrokerInterface $broker
	 * @param BrokerMessageCacheServiceInterface $brokerMessageCacheService
	 * @param TrophyRepository $trophyRepository
	 */
	public function __construct(
		BrokerInterface $broker,
		BrokerMessageCacheServiceInterface $brokerMessageCacheService,
		TrophyRepository $trophyRepository
	) {
		$this->serializer = app('Serializer');
		$this->broker = $broker;
		$this->brokerMessageCacheService = $brokerMessageCacheService;
		$this->trophyRepository = $trophyRepository;
	}

	/**
	 * @param TrophyProjectorEvent $event
	 * @throws ProjectionException
	 */
	public function handle(TrophyProjectorEvent $event)
	{
		if (! $this->brokerMessageCacheService->hasTournamentInfo($event->trophy->getTournamentId())) {
			$message = (new Message())
				->setHeaders(
					(new Headers())
						->setKey(self::BROKER_EVENT_KEY)
						->setId($event->trophy->getTournamentId())
						->setDestination(config('broker.services.competition_name'))
						->setSource(config('broker.services.team_name'))
						->setDate(Carbon::now()->toDateTimeString())
				)->setBody([
					'entity' => config('broker.services.tournament_name'),
					'id' => $event->trophy->getTournamentId()
				]);
			$this->broker->flushMessages()->addMessage(
				self::BROKER_EVENT_KEY,
				$this->serializer->serialize($message, 'json')
			)->produceMessage(config('broker.topics.question'));
			return;
		}
		$tournamentInfo = $this->brokerMessageCacheService->getTournamentInfo($event->trophy->getTournamentId());
		$event->trophy
			->setCompetitionName($tournamentInfo['competitionName'])
			->setTournamentSeason($tournamentInfo['season']);
		try {
			$this->trophyRepository->persist($event->trophy);
		} catch (DynamoDBRepositoryException $exception) {
			throw new ProjectionException('Failed to update trophy.', $exception->getCode(), $exception);
		}
	}
}