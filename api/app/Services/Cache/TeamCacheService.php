<?php


namespace App\Services\Cache;


use App\Models\ReadModels\Team;
use App\Services\Cache\Interfaces\TeamCacheServiceInterface;


/**
 * Class TeamCacheService
 * @package App\Services\Cache
 */
class TeamCacheService extends CacheService implements TeamCacheServiceInterface
{
	/**
	 * @param string $teamId
	 * @return string
	 */
	private static function getTeamKey(string $teamId): string
	{
		return sprintf(self::TEAM_KEY, $teamId);
	}

	/**
	 * @param Team $team
	 */
	public function putTeam(Team $team): void
	{
		$this->put(self::getTeamKey($team->getId()), $team);
	}

	/**
	 * @param string $teamId
	 * @return Team
	 */
	public function getTeam(string $teamId): ?Team
	{
		return $this->get(self::getTeamKey($teamId));
	}
}