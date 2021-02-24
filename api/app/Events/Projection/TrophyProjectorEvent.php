<?php


namespace App\Events\Projection;


use App\Events\Event;
use App\Models\ReadModels\Trophy;


/**
 * Class TrophyProjectorEvent
 * @package App\Events\Projection
 */
class TrophyProjectorEvent extends Event
{
	public Trophy $trophy;
	public string $eventName;

	/**
	 * TrophyProjectorEvent constructor.
	 * @param Trophy $trophy
	 * @param string $eventName
	 */
	public function __construct(Trophy $trophy, string $eventName)
	{
		$this->trophy = $trophy;
		$this->eventName = $eventName;
	}
}