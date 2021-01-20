<?php


namespace App\Events\Projection;


use App\Events\Event;


/**
 * Class MatchWasCreatedProjectorEvent
 * @package App\Events\Projection
 */
class MatchWasCreatedProjectorEvent extends Event
{
	public string $competitionId;

	/**
	 * MatchWasCreatedProjectorEvent constructor.
	 * @param string $competitionId
	 */
	public function __construct(string $competitionId)
	{
		$this->competitionId = $competitionId;
	}
}