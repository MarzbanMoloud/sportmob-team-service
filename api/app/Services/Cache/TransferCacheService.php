<?php


namespace App\Services\Cache;


use App\Services\Cache\Interfaces\TransferCacheServiceInterface;


/**
 * Class TransferCacheService
 * @package App\Services\Cache
 */
class TransferCacheService extends CacheService implements TransferCacheServiceInterface
{
	/**
	 * @param string $teamId
	 * @param string $season
	 * @return string
	 */
	public static function getTransferByTeamKey(string $teamId, string $season): string
	{
		return sprintf(self::TRANSFER_BY_TEAM_KEY, $teamId, $season);
	}

	/**
	 * @param string $playerId
	 * @return string
	 */
	public static function getTransferByPlayerKey(string $playerId): string
	{
		return sprintf(self::TRANSFER_BY_PLAYER_KEY, $playerId);
	}

	/**
	 * @param string $teamId
	 * @return string
	 */
	public static function getAllSeasonsByTeamKey(string $teamId): string
	{
		return sprintf(self::TRANSFER_All_SEASONS_BY_TEAM_KEY, $teamId);
	}

	/**
	 * @param string $action
	 * @param string $user
	 * @param string $transfer
	 * @return string
	 */
	public static function getUserActionTransferKey(string $action, string $user, string $transfer): string
	{
		return sprintf(self::USER_ACTION_TRANSFER_KEY, $action, $user, $transfer);
	}

	/**
	 * @param string $teamId
	 * @param string $season
	 * @param $function
	 * @return mixed
	 */
	public function rememberForeverTransferByTeam(string $teamId, string $season, $function)
	{
		return $this->rememberForever(self::getTransferByTeamKey($teamId, $season), $function);
	}

	/**
	 * @param string $teamId
	 * @param $function
	 * @return mixed
	 */
	public function rememberForeverAllSeasonsByTeam(string $teamId, $function)
	{
		return $this->rememberForever(self::getAllSeasonsByTeamKey($teamId), $function);
	}

	/**
	 * @param string $playerId
	 * @param $function
	 * @return mixed
	 */
	public function rememberForeverTransferByPlayer(string $playerId, $function)
	{
		return $this->rememberForever(self::getTransferByPlayerKey($playerId), $function);
	}

	/**
	 * @param string $action
	 * @param string $user
	 * @param string $transfer
	 * @return mixed|void
	 */
	public function putUserActionTransfer(string $action, string $user, string $transfer)
	{
		$this->put(self::getUserActionTransferKey($action, $user, $transfer), true);
	}

	/**
	 * @param string $action
	 * @param string $user
	 * @param string $transfer
	 * @return bool|mixed
	 */
	public function hasUserActionTransfer(string $action, string $user, string $transfer)
	{
		return $this->hasKey(self::getUserActionTransferKey($action, $user, $transfer));
	}
}