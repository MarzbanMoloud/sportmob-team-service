<?php


namespace App\Services\Cache\Interfaces;


/**
 * Interface TeamsMatchCacheServiceInterface
 * @package App\Services\Cache\Interfaces
 */
interface TeamsMatchCacheServiceInterface
{
	const TEAMS_MATCH_BY_TEAM_KEY = 'teams_match_by_team_%s_status_%s';

	/**
	 * @param string $teamId
	 * @param string $status
	 * @param $function
	 * @return mixed
	 */
	public function rememberForeverTeamsMatchByTeamId(string $teamId, string $status, $function);

	/**
	 * @param string $teamId
	 * @param string $status
	 * @return mixed
	 */
	public function hasTeamsMatchByTeamId(string $teamId, string $status);
}