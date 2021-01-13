<?php


namespace App\Services\EventStrategy;


use App\Projections\Projector\TrophyProjector;
use App\Services\EventStrategy\Interfaces\EventInterface;
use App\ValueObjects\Broker\Mediator\Message;
use App\ValueObjects\Broker\Mediator\MessageBody;


/**
 * Class TeamBecameWinner
 * @package App\Services\EventStrategy
 */
class TeamBecameWinner implements EventInterface
{
	private TrophyProjector $trophyProjector;

	/**
	 * TeamBecameWinner constructor.
	 * @param TrophyProjector $trophyProjector
	 */
	public function __construct(TrophyProjector $trophyProjector)
	{
		$this->trophyProjector = $trophyProjector;
	}

	/**
	 * @param Message $message
	 * @return bool
	 */
	public function support(Message $message): bool
	{
		return $message->getHeaders()->getEvent() == config('mediator-event.events.team_became_winner');
	}

	/**
	 * @param MessageBody $body
	 * @throws \App\Exceptions\Projection\ProjectionException
	 */
	public function handle(MessageBody $body): void
	{
		$this->trophyProjector->applyTeamBecameWinner($body);
	}
}