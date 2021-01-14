<?php


namespace App\Services\Cache\Interfaces;


/**
 * Interface TrophyCacheServiceInterface
 * @package App\Services\Cache\Interfaces
 */
interface TrophyCacheServiceInterface
{
	const TROPHY_BY_TEAM_KEY = 'trophies_by_team_%s';
	const TROPHY_BY_COMPETITION_KEY = 'trophies_by_competition_%s';

	/**
	 * @param string $teamId
	 * @param $function
	 * @return mixed
	 */
	public function rememberForeverTrophiesByTeam(string $teamId, $function);

	/**
	 * @param string $competitionId
	 * @param $function
	 * @return mixed
	 */
	public function rememberForeverTrophiesByCompetition(string $competitionId, $function);
}