<?php


namespace App\Events\Projection;


use App\Events\Event;
use App\Models\ReadModels\Team;


/**
 * Class TeamWasCreatedProjectorEvent
 * @package App\Events\Projection
 */
class TeamWasCreatedProjectorEvent extends Event
{
	public Team $team;

	/**
	 * TeamWasCreatedProjectorEvent constructor.
	 * @param Team $team
	 */
	public function __construct(Team $team)
	{
		$this->team = $team;
	}
}