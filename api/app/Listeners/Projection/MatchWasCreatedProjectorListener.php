<?php


namespace App\Listeners\Projection;


use App\Events\Projection\MatchWasCreatedProjectorEvent;
use App\Exceptions\DynamoDB\DynamoDBRepositoryException;
use App\Models\ReadModels\TeamsMatch;
use App\Models\Repositories\TeamsMatchRepository;
use App\Services\BrokerInterface;
use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;
use App\Services\Logger\Event;
use App\ValueObjects\Broker\CommandQuery\Headers;
use App\ValueObjects\Broker\CommandQuery\Message;
use App\ValueObjects\Broker\Mediator\Message as MediatorMessage;
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
	private string $eventName;

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
		$identifier = $event->mediatorMessage->getBody()->getIdentifiers();
		$this->eventName = config('mediator-event.events.match_was_created');
		if (!$this->brokerMessageCacheService->hasCompetitionName($identifier['competition'])) {
			$message = (new Message())
				->setHeaders(
					(new Headers())
						->setEventId($event->mediatorMessage->getHeaders()->getId())
						->setKey(self::BROKER_EVENT_KEY)
						->setId(sprintf('%s#%s#%s', $identifier['match'], $identifier['home'], $identifier['away']))
						->setDestination(config('broker.services.competition_name'))
						->setSource(config('broker.services.team_name'))
						->setDate(Carbon::now()->format('c'))
				)->setBody([
					'entity' => config('broker.services.competition_name'),
					'id' => $identifier['competition']
				]);
			$this->broker->flushMessages()->addMessage(
				self::BROKER_EVENT_KEY,
				$this->serializer->serialize($message, 'json')
			)->produceMessage(config('broker.topics.question_competition'));
			Event::needToAsk($message, $this->eventName, self::BROKER_EVENT_KEY, config('broker.services.competition_name'));
			return;
		}
		$competitionName = $this->brokerMessageCacheService->getCompetitionName($identifier['competition']);
		$this->updateTeamsMatch($identifier['match'], $identifier['home'], $competitionName, $event->mediatorMessage);
		$this->updateTeamsMatch($identifier['match'], $identifier['away'], $competitionName, $event->mediatorMessage);
	}

	/**
	 * @param string $matchId
	 * @param string $teamId
	 * @param string $competitionName
	 * @param MediatorMessage $mediatorMessage
	 */
	private function updateTeamsMatch(string $matchId, string $teamId, string $competitionName, MediatorMessage $mediatorMessage): void
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
			Event::failed($mediatorMessage, $this->eventName, 'Failed to update teamsMatch.');
			$this->sentryHub->captureException($exception);
		}
	}
}