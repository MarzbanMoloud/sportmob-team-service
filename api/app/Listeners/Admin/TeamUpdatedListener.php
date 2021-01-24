<?php


namespace App\Listeners\Admin;


use App\Events\Admin\TeamUpdatedEvent;
use App\Services\BrokerInterface;
use App\Services\Cache\Interfaces\TeamCacheServiceInterface;
use App\Services\Cache\TeamCacheService;
use App\ValueObjects\Broker\Mediator\Message as MediatorMessage;
use App\ValueObjects\Broker\Mediator\MessageBody;
use App\ValueObjects\Broker\Mediator\MessageHeader;
use DateTimeImmutable;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class TeamUpdatedListener
 * @package App\Listeners\Admin
 */
class TeamUpdatedListener
{
	private SerializerInterface $serializer;
	private BrokerInterface $broker;
	private TeamCacheServiceInterface $teamCacheService;

	/**
	 * TeamUpdatedListener constructor.
	 * @param BrokerInterface $broker
	 * @param TeamCacheServiceInterface $teamCacheService
	 */
	public function __construct(
		BrokerInterface $broker,
		TeamCacheServiceInterface $teamCacheService
	) {
		$this->serializer = app('Serializer');
		$this->broker = $broker;
		$this->teamCacheService = $teamCacheService;
	}

	/**
	 * @param TeamUpdatedEvent $event
	 */
	public function handle(TeamUpdatedEvent $event)
	{
		$messageHeader = (new MessageHeader(
			config('mediator-event.events.team_was_updated'),
			1,
			new DateTimeImmutable()
		));
		$messageBody = (new MessageBody(
			["team" => $event->team->getId()],
			[
				'name' => [
					"official" => $event->team->getName()->getOfficial(),
					"original" => $event->team->getName()->getOriginal(),
					"short" => $event->team->getName()->getShort(),
				]
			]
		));
		$message = (new MediatorMessage())
			->setHeaders($messageHeader)
			->setBody($messageBody);
		$this->broker->addMessage(
			config('mediator-event.events.team_was_updated'),
			$this->serializer->serialize($message, 'json')
		)->produceMessage(config('broker.topics.event'));
		/**
		 * Remove Team cache.
		 */
		$this->teamCacheService->forget(TeamCacheService::getTeamKey($event->team->getId()));
	}
}