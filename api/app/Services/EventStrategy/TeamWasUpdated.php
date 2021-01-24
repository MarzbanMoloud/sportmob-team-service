<?php


namespace App\Services\EventStrategy;


use App\Projections\Projector\TeamProjector;
use App\Services\EventStrategy\Interfaces\EventInterface;
use App\ValueObjects\Broker\Mediator\Message;
use App\ValueObjects\Broker\Mediator\MessageBody;


/**
 * Class TeamWasUpdated
 * @package App\Services\EventStrategy
 */
class TeamWasUpdated implements EventInterface
{
	private TeamProjector $teamProjector;

	/**
	 * TeamWasUpdated constructor.
	 * @param TeamProjector $teamProjector
	 */
	public function __construct(TeamProjector $teamProjector)
	{
		$this->teamProjector = $teamProjector;
	}

	/**
	 * @param Message $message
	 * @return bool
	 */
	public function support(Message $message): bool
	{
		return $message->getHeaders()->getEvent() == config('mediator-event.events.team_was_updated');
	}

	/**
	 * @param MessageBody $body
	 * @throws \App\Exceptions\Projection\ProjectionException
	 */
	public function handle(MessageBody $body): void
	{
		$this->teamProjector->applyTeamWasUpdated($body);
	}
}