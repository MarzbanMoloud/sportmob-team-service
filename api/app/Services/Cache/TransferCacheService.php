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
	 * @param string $id
	 * @return string
	 */
	public static function getTransferByPersonKey(string $id): string
	{
		return sprintf(self::TRANSFER_BY_PERSON_KEY, $id);
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
	public function rememberForeverTransfersByTeam(string $teamId, string $season, $function)
	{
		return $this->rememberForever(self::getTransferByTeamKey($teamId, $season), $function);
	}

	/**
	 * @param string $teamId
	 * @param string $season
	 * @param array $transfers
	 * @return mixed|void
	 */
	public function putTransfersByTeam(string $teamId, string $season, array $transfers)
	{
		$this->put(self::getTransferByTeamKey($teamId, $season), $transfers);
	}

	/**
	 * @param string $id
	 * @param $function
	 * @return mixed
	 */
	public function rememberForeverTransfersByPerson(string $id, $function)
	{
		return $this->rememberForever(self::getTransferByPersonKey($id), $function);
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