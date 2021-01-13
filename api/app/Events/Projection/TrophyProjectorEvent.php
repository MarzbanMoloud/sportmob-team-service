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

	/**
	 * TrophyProjectorEvent constructor.
	 * @param Trophy $trophy
	 */
	public function __construct(Trophy $trophy)
	{
		$this->trophy = $trophy;
	}
}