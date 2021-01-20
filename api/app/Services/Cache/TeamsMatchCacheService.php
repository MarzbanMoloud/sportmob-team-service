<?php


namespace App\Services\Cache;


use App\Services\Cache\Interfaces\TeamsMatchCacheServiceInterface;


/**
 * Class TeamsMatchCacheService
 * @package App\Services\Cache
 */
class TeamsMatchCacheService extends CacheService implements TeamsMatchCacheServiceInterface
{
	/**
	 * @param string $teamId
	 * @return string
	 */
	public static function getTeamsMatchOverviewByTeamKey(string $teamId): string
	{
		return sprintf(self::TEAMS_MATCH_OVERVIEW_KEY, $teamId);
	}

	/**
	 * @param string $teamId
	 * @param $function
	 * @return mixed
	 */
	public function rememberForeverTeamsMatchOverviewByTeam(string $teamId, $function)
	{
		return $this->rememberForever(self::getTeamsMatchOverviewByTeamKey($teamId), $function);
	}

	/**
	 * @param string $teamId
	 * @return bool|mixed
	 */
	public function hasTeamsMatchOverviewByTeam(string $teamId)
	{
		return $this->hasKey(self::getTeamsMatchOverviewByTeamKey($teamId));
	}
}