<?php


namespace App\Events\Admin;


use App\Events\Event;
use App\Models\ReadModels\Team;


/**
 * Class TeamUpdatedEvent
 * @package App\Events\Admin
 */
class TeamUpdatedEvent extends Event
{
	/**
	 * @var Team
	 */
	public Team $team;

	/**
	 * TeamUpdatedEvent constructor.
	 * @param Team $team
	 */
	public function __construct(Team $team)
	{
		$this->team = $team;
	}
}