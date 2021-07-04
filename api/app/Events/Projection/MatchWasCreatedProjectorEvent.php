<?php


namespace App\Events\Projection;


use App\Events\Event;
use App\ValueObjects\Broker\Mediator\Message;


/**
 * Class MatchWasCreatedProjectorEvent
 * @package App\Events\Projection
 */
class MatchWasCreatedProjectorEvent extends Event
{
	public Message $mediatorMessage;

	/**
	 * MatchWasCreatedProjectorEvent constructor.
	 * @param Message $mediatorMessage
	 */
	public function __construct(Message $mediatorMessage)
	{
		$this->mediatorMessage = $mediatorMessage;
	}
}