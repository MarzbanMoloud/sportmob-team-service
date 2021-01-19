<?php


namespace App\Services\EventStrategy;


use App\Projections\Projector\MatchProjector;
use App\Services\EventStrategy\Interfaces\EventInterface;
use App\ValueObjects\Broker\Mediator\Message;
use App\ValueObjects\Broker\Mediator\MessageBody;


/**
 * Class MatchStatusChanged
 * @package App\Services\EventStrategy
 */
class MatchStatusChanged implements EventInterface
{
	private MatchProjector $matchProjector;

	/**
	 * MatchStatusChanged constructor.
	 * @param MatchProjector $matchProjector
	 */
	public function __construct(MatchProjector $matchProjector)
	{
		$this->matchProjector = $matchProjector;
	}

	/**
	 * @param Message $message
	 * @return bool
	 */
	public function support(Message $message): bool
	{
		return $message->getHeaders()->getEvent() == config('mediator-event.events.match_status_changed');
	}

	/**
	 * @param MessageBody $body
	 * @throws \App\Exceptions\Projection\ProjectionException
	 */
	public function handle(MessageBody $body): void
	{
		$this->matchProjector->applyMatchStatusChanged($body);
	}
}