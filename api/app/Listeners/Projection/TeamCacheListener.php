<?php


namespace App\Listeners\Projection;


use App\Events\Projection\TeamWasCreatedProjectorEvent;
use App\Services\Cache\Interfaces\TeamCacheServiceInterface;


/**
 * Class TeamCacheListener
 * @package App\Listeners\Projection
 */
class TeamCacheListener
{
	private TeamCacheServiceInterface $teamCacheService;

	/**
	 * TeamCacheListener constructor.
	 * @param TeamCacheServiceInterface $teamCacheService
	 */
	public function __construct(TeamCacheServiceInterface $teamCacheService)
	{
		$this->teamCacheService = $teamCacheService;
	}

	/**
	 * @param TeamWasCreatedProjectorEvent $event
	 */
	public function handle(TeamWasCreatedProjectorEvent $event): void
	{
		$this->teamCacheService->putTeam($event->team);
	}
}