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
use Psr\Log\LoggerInterface;
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
	private LoggerInterface $logger;
	private string $eventName;

	/**
	 * MatchWasCreatedProjectorListener constructor.
	 * @param BrokerInterface $broker
	 * @param BrokerMessageCacheServiceInterface $brokerMessageCacheService
	 * @param TeamsMatchRepository $teamsMatchRepository
	 * @param HubInterface $sentryHub
	 * @param SerializerInterface $serializer
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		BrokerInterface $broker,
		BrokerMessageCacheServiceInterface $brokerMessageCacheService,
		TeamsMatchRepository $teamsMatchRepository,
		HubInterface $sentryHub,
		SerializerInterface $serializer,
		LoggerInterface $logger
	) {
		$this->serializer = $serializer;
		$this->broker = $broker;
		$this->brokerMessageCacheService = $brokerMessageCacheService;
		$this->teamsMatchRepository = $teamsMatchRepository;
		$this->sentryHub = $sentryHub;
		$this->logger = $logger;
	}

	/**
	 * @param MatchWasCreatedProjectorEvent $event
	 */
	public function handle(MatchWasCreatedProjectorEvent $event)
	{
		$this->eventName = config('mediator-event.events.match_was_created');
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

			$this->logger->alert(
				sprintf(
					"%s handler needs to ask %s from %s",
					$this->eventName,
					self::BROKER_EVENT_KEY,
					config('broker.services.competition_name')
				),
				$this->serializer->normalize($message, 'array')
			);
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
			$this->logger->alert(
				sprintf(
					"%s handler failed because of %s",
					$this->eventName,
					'Failed to update teamsMatch.'
				), $this->serializer->normalize($teamsMatchItem, 'array')
			);
			$this->sentryHub->captureException($exception);
		}
	}
}