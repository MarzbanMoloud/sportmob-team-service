<?php


namespace App\Services\Cache;


use App\Services\Cache\Interfaces\BrokerMessageCacheServiceInterface;


/**
 * Class BrokerMessageCacheService
 * @package App\Services\Cache
 */
class BrokerMessageCacheService extends CacheService implements BrokerMessageCacheServiceInterface
{
	/**
	 * @param string $playerId
	 * @return string
	 */
	private static function getPlayerInfoKey(string $playerId)
	{
		return sprintf(self::PLAYER_INFO_NAME_KEY, $playerId);
	}

	/**
	 * @param string $tournamentId
	 * @return string
	 */
	private static function getTournamentInfoKey(string $tournamentId)
	{
		return sprintf(self::TOURNAMENT_INFO_NAME_KEY, $tournamentId);
	}

	/**
	 * @param array $player
	 * @return mixed|void
	 */
	public function putPlayerInfo(array $player)
	{
		$this->put(self::getPlayerInfoKey($player['id']), $player);
	}

	/**
	 * @param string $playerId
	 * @return bool|mixed
	 */
	public function hasPlayerInfo(string $playerId)
	{
		return $this->hasKey(self::getPlayerInfoKey($playerId));
	}

	/**
	 * @param string $playerId
	 * @return mixed
	 */
	public function getPlayerInfo(string $playerId)
	{
		return $this->get(self::getPlayerInfoKey($playerId));
	}

	/**
	 * @param array $tournament
	 * @return mixed|void
	 */
	public function putTournamentInfo(array $tournament)
	{
		$this->put(self::getTournamentInfoKey($tournament['id']), $tournament);
	}

	/**
	 * @param string $tournamentId
	 * @return bool|mixed
	 */
	public function hasTournamentInfo(string $tournamentId)
	{
		return $this->hasKey(self::getTournamentInfoKey($tournamentId));
	}

	/**
	 * @param string $tournamentId
	 * @return mixed
	 */
	public function getTournamentInfo(string $tournamentId)
	{
		return $this->get(self::getTournamentInfoKey($tournamentId));
	}
}