<?php


namespace App\Listeners\Projection;


use App\Events\Projection\MatchWasCreatedProjectorEvent;
use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Models\ReadModels\TeamsMatch;
use App\Models\Repositories\TeamsMatchRepository;
use App\Services\BrokerInterface;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
use App\ValueObjects\Broker\CommandQuery\Headers;
use App\ValueObjects\Broker\CommandQuery\Message;
use Carbon\Carbon;
use Sentry\State\HubInterface;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class MatchWasCreatedProjectorListener
 * @package App\Listeners\Projection
 */
class MatchWasCreatedProjectorListener
{
	const BROKER_EVENT_KEY = 'MatchWasCreatedUpdateInfo';

	private BrokerInterface $broker;
	private BrokerMessageCacheServiceInterface $brokerMessageCacheService;
	private TeamsMatchRepository $teamsMatchRepository;
	private SerializerInterface $serializer;
	private HubInterface $sentryHub;

	/**
	 * MatchWasCreatedProjectorListener constructor.
	 * @param BrokerInterface $broker
	 * @param BrokerMessageCacheServiceInterface $brokerMessageCacheService
	 * @param TeamsMatchRepository $teamsMatchRepository
	 * @param HubInterface $sentryHub
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		BrokerInterface $broker,
		BrokerMessageCacheServiceInterface $brokerMessageCacheService,
		TeamsMatchRepository $teamsMatchRepository,
		HubInterface $sentryHub,
		SerializerInterface $serializer
	) {
		$this->serializer = $serializer;
		$this->broker = $broker;
		$this->brokerMessageCacheService = $brokerMessageCacheService;
		$this->teamsMatchRepository = $teamsMatchRepository;
		$this->sentryHub = $sentryHub;
	}

	/**
	 * @param MatchWasCreatedProjectorEvent $event
	 */
	public function handle(MatchWasCreatedProjectorEvent $event)
	{
		if (!$this->brokerMessageCacheService->hasCompetitionName($event->identifier['competition'])) {
			$message = (new Message())
				->setHeaders(
					(new Headers())
						->setKey(self::BROKER_EVENT_KEY)
						->setId(sprintf('%s#%s#%s', $event->identifier['match'], $event->identifier['home'],
							$event->identifier['away']))
						->setDestination(config('broker.services.competition_name'))
						->setSource(config('broker.services.team_name'))
						->setDate(Carbon::now()->toDateTimeString())
				)->setBody([
					'entity' => config('broker.services.competition_name'),
					'id' => $event->identifier['competition']
				]);
			$this->broker->flushMessages()->addMessage(
				self::BROKER_EVENT_KEY,
				$this->serializer->serialize($message, 'json')
			)->produceMessage(config('broker.topics.question'));
			return;
		}
		$competitionName = $this->brokerMessageCacheService->getCompetitionName($event->identifier['competition']);
		$this->updateTeamsMatch($event->identifier['match'], $event->identifier['home'], $competitionName);
		$this->updateTeamsMatch($event->identifier['match'], $event->identifier['away'], $competitionName);
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