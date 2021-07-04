<?php


namespace App\Services\EventStrategy;


use App\Projections\Projector\TeamProjector;
use App\Services\EventStrategy\Interfaces\EventInterface;
use App\Services\Logger\Event;
use App\ValueObjects\Broker\Mediator\Message;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class TeamWasCreated
 * @package App\Services\EventStrategy
 */
class TeamWasCreated implements EventInterface
{
	private TeamProjector $teamProjector;
	private SerializerInterface $serializer;

	/**
	 * TeamWasCreated constructor.
	 * @param TeamProjector $teamProjector
	 * @param SerializerInterface $serializer
	 */
	public function __construct(
		TeamProjector $teamProjector,
		SerializerInterface $serializer
	) {
		$this->teamProjector = $teamProjector;
		$this->serializer = $serializer;
	}

	/**
	 * @param Message $message
	 * @throws \App\Exceptions\Projection\ProjectionException
	 */
	public function handle(Message $message): void
	{
		Event::handled($message, config('mediator-event.events.team_was_created'), __CLASS__);
		$this->teamProjector->applyTeamWasCreated($message);
	}
}