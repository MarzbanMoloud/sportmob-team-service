<?php


namespace App\Services\Cache\Interfaces;


use App\Models\ReadModels\Team;


/**
 * Interface TeamCacheServiceInterface
 * @package App\Services\Cache\Interfaces
 */
interface TeamCacheServiceInterface
{
	const TEAM_KEY = 'team_%s';

	/**
	 * @param Team $team
	 */
	public function putTeam(Team $team): void;

	/**
	 * @param string $teamId
	 * @return Team
	 */
	public function getTeam(string $teamId): ?Team;
}