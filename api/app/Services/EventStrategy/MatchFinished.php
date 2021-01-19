<?php


namespace App\Services\EventStrategy;


use App\Projections\Projector\MatchProjector;
use App\Services\EventStrategy\Interfaces\EventInterface;
use App\ValueObjects\Broker\Mediator\Message;
use App\ValueObjects\Broker\Mediator\MessageBody;


/**
 * Class MatchFinished
 * @package App\Services\EventStrategy
 */
class MatchFinished implements EventInterface
{
	private MatchProjector $matchProjector;

	/**
	 * MatchFinished constructor.
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
		return $message->getHeaders()->getEvent() == config('mediator-event.events.match_finished');
	}

	/**
	 * @param MessageBody $body
	 * @throws \App\Exceptions\Projection\ProjectionException
	 */
	public function handle(MessageBody $body): void
	{
		$this->matchProjector->applyMatchFinished($body);
	}
}