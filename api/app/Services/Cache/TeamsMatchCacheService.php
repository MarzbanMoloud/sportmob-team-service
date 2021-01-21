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
	 * @param string $status
	 * @return string
	 */
	public static function getTeamsMatchByTeamIdKey(string $teamId, string $status): string
	{
		return sprintf(self::TEAMS_MATCH_BY_TEAM_KEY, $teamId, $status);
	}

	/**
	 * @param string $teamId
	 * @param string $status
	 * @param $function
	 * @return mixed
	 */
	public function rememberForeverTeamsMatchByTeamId(string $teamId, string $status, $function)
	{
		return $this->rememberForever(self::getTeamsMatchByTeamIdKey($teamId, $status), $function);
	}

	/**
	 * @param string $teamId
	 * @param string $status
	 * @return bool|mixed
	 */
	public function hasTeamsMatchByTeamId(string $teamId, string $status)
	{
		return $this->hasKey(self::getTeamsMatchByTeamIdKey($teamId, $status));
	}
}