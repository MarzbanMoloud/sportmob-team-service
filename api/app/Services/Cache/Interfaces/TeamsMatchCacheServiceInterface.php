<?php


namespace App\Services\Cache\Interfaces;


/**
 * Interface TeamsMatchCacheServiceInterface
 * @package App\Services\Cache\Interfaces
 */
interface TeamsMatchCacheServiceInterface
{
	const TEAMS_MATCH_OVERVIEW_KEY = 'teams_match_overview_%s';

	/**
	 * @param string $teamId
	 * @param $function
	 * @return mixed
	 */
	public function rememberForeverTeamsMatchOverviewByTeam(string $teamId, $function);

	/**
	 * @param string $teamId
	 * @return mixed
	 */
	public function hasTeamsMatchOverviewByTeam(string $teamId);
}