<?php


namespace App\Services\Cache\Interfaces;


/**
 * Interface TransferCacheServiceInterface
 * @package App\Services\Cache\Interfaces
 */
interface TransferCacheServiceInterface
{
	const TRANSFER_BY_TEAM_KEY = 'transfer_team_%s_season_%s_transfers';
	const TRANSFER_BY_PERSON_KEY = 'transfer_person_%s_transfers';
	const USER_ACTION_TRANSFER_KEY = 'transfer_user_action_%s_%s_%s';

	/**
	 * @param string $teamId
	 * @param string $season
	 * @param $function
	 * @return mixed
	 */
	public function rememberForeverTransfersByTeam(string $teamId, string $season, $function);

	/**
	 * @param string $teamId
	 * @param string $season
	 * @param array $transfers
	 * @return mixed
	 */
	public function putTransfersByTeam(string $teamId, string $season, array $transfers);

	/**
	 * @param string $id
	 * @param $function
	 * @return mixed
	 */
	public function rememberForeverTransfersByPerson(string $id, $function);

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