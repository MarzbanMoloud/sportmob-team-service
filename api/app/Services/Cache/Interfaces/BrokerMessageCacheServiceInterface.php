<?php


namespace App\Services\Cache\Interfaces;


/**
 * Interface BrokerMessageCacheServiceInterface
 * @package App\Services\Cache\Interfaces
 */
interface BrokerMessageCacheServiceInterface
{
	const PLAYER_INFO_NAME_KEY = 'player_info_%s';
	const TOURNAMENT_INFO_NAME_KEY = 'tournament_info_%s';
	const COMPETITION_NAME_KEY = 'competition_name_%s';

	/**
	 * @param array $player
	 * @return mixed
	 */
	public function putPlayerInfo(array $player);

	/**
	 * @param string $playerId
	 * @return mixed
	 */
	public function hasPlayerInfo(string $playerId);

	/**
	 * @param string $playerId
	 * @return mixed
	 */
	public function getPlayerInfo(string $playerId);

	/**
	 * @param array $tournament
	 * @return mixed
	 */
	public function putTournamentInfo(array $tournament);

	/**
	 * @param string $tournamentId
	 * @return mixed
	 */
	public function hasTournamentInfo(string $tournamentId);

	/**
	 * @param string $tournamentId
	 * @return mixed
	 */
	public function getTournamentInfo(string $tournamentId);

	/**
	 * @param array $competition
	 * @return mixed
	 */
	public function putCompetitionName(array $competition);

	/**
	 * @param string $competitionId
	 * @return mixed
	 */
	public function hasCompetitionName(string $competitionId);

	/**
	 * @param string $competitionId
	 * @return mixed
	 */
	public function getCompetitionName(string $competitionId);
}