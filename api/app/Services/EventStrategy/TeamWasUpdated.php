<?php


namespace App\Services\EventStrategy;


use App\Projections\Projector\TeamProjector;
use App\Services\EventStrategy\Interfaces\EventInterface;
use App\Services\Logger\Event;
use App\ValueObjects\Broker\Mediator\MessageBody;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class TeamWasUpdated
 * @package App\Services\EventStrategy
 */
class TeamWasUpdated implements EventInterface
{
	private TeamProjector $teamProjector;
	private SerializerInterface $serializer;

	/**
	 * TeamWasUpdated constructor.
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
	 * @param MessageBody $body
	 * @throws \App\Exceptions\Projection\ProjectionException
	 */
	public function handle(MessageBody $body): void
	{
		Event::handled($body, config('mediator-event.events.team_was_updated'), __CLASS__);
		$this->teamProjector->applyTeamWasUpdated($body);
	}
}