<?php


namespace App\Events\Projection;


use App\Events\Event;
use App\Models\ReadModels\Trophy;
use App\ValueObjects\Broker\Mediator\Message;


/**
 * Class TrophyProjectorEvent
 * @package App\Events\Projection
 */
class TrophyProjectorEvent extends Event
{
	public Trophy $trophy;
	public string $eventName;
	public Message $mediatorMessage;

	/**
	 * TrophyProjectorEvent constructor.
	 * @param Trophy $trophy
	 * @param string $eventName
	 * @param Message $mediatorMessage
	 */
	public function __construct(Trophy $trophy, string $eventName, Message $mediatorMessage)
	{
		$this->trophy = $trophy;
		$this->eventName = $eventName;
		$this->mediatorMessage = $mediatorMessage;
	}
}