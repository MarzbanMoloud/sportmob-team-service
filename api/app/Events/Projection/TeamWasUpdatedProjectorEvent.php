<?php


namespace App\Events\Projection;


use App\Events\Event;
use App\Models\ReadModels\Team;


/**
 * Class TeamWasUpdatedProjectorEvent
 * @package App\Events\Projection
 */
class TeamWasUpdatedProjectorEvent extends Event
{
	public Team $team;

	/**
	 * TeamWasUpdatedProjectorEvent constructor.
	 * @param Team $team
	 */
	public function __construct(Team $team)
	{
		$this->team = $team;
	}
}