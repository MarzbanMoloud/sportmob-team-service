<?php


namespace App\Services\Cache\Interfaces;


/**
 * Interface TransferCacheServiceInterface
 * @package App\Services\Cache\Interfaces
 */
interface TransferCacheServiceInterface
{
	const TRANSFER_BY_TEAM_KEY = 'transfer_by_team_%s_season_%s';
	const TRANSFER_BY_PERSON_KEY = 'transfer_by_person_%s';
	const TRANSFER_All_SEASONS_BY_TEAM_KEY = 'transfer_all_seasons_by_team_%s';
	const USER_ACTION_TRANSFER_KEY = 'transfer_user_action_%s_%s_%s';

	/**
	 * @param string $teamId
	 * @param string $season
	 * @param $function
	 * @return mixed
	 */
	public function rememberForeverTransferByTeam(string $teamId, string $season, $function);

	/**
	 * @param string $teamId
	 * @param $function
	 * @return mixed
	 */
	public function rememberForeverAllSeasonsByTeam(string $teamId, $function);

	/**
	 * @param string $id
	 * @param $function
	 * @return mixed
	 */
	public function rememberForeverTransferByPerson(string $id, $function);

	/**
	 * @param string $action
	 * @param string $user
	 * @param string $transfer
	 * @return mixed
	 */
	public function putUserActionTransfer(string $action, string $user, string $transfer);

	/**
	 * @param string $action
	 * @param string $user
	 * @param string $transfer
	 * @return mixed
	 */
	public function hasUserActionTransfer(string $action, string $user, string $transfer);
}