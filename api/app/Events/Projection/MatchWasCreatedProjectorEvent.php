<?php


namespace App\Events\Projection;


use App\Events\Event;


/**
 * Class MatchWasCreatedProjectorEvent
 * @package App\Events\Projection
 */
class MatchWasCreatedProjectorEvent extends Event
{
	public array $identifier;

	/**
	 * MatchWasCreatedProjectorEvent constructor.
	 * @param array $identifier
	 */
	public function __construct(array $identifier)
	{
		$this->identifier = $identifier;
	}
}