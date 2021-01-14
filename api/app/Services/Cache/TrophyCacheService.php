<?php


namespace App\Services\Cache;


use App\Services\Cache\Interfaces\TrophyCacheServiceInterface;


/**
 * Class TrophyCacheService
 * @package App\Services\Cache
 */
class TrophyCacheService extends CacheService implements TrophyCacheServiceInterface
{
	/**
	 * @param string $teamId
	 * @return string
	 */
	private static function getTrophyByTeamKey(string $teamId): string
	{
		return sprintf(self::TROPHY_BY_TEAM_KEY, $teamId);
	}

	/**
	 * @param string $teamId
	 * @return string
	 */
	private static function getTrophyByCompetitionKey(string $teamId): string
	{
		return sprintf(self::TROPHY_BY_COMPETITION_KEY, $teamId);
	}

	/**
	 * @param string $teamId
	 * @param $function
	 * @return mixed
	 */
	public function rememberForeverTrophiesByTeam(string $teamId, $function)
	{
		return $this->rememberForever(self::getTrophyByTeamKey($teamId), $function);
	}

	/**
	 * @param string $competitionId
	 * @param $function
	 * @return mixed
	 */
	public function rememberForeverTrophiesByCompetition(string $competitionId, $function)
	{
		return $this->rememberForever(self::getTrophyByCompetitionKey($competitionId), $function);
	}
}