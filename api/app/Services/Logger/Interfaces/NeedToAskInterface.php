<?php

namespace App\Services\Logger\Interfaces;

/**
 * Interface NeedToAskInterface
 * @package App\Services\Logger\Interfaces
 */
interface NeedToAskInterface
{
	/**
	 * @param string $eventName
	 * @param string $questionKey
	 * @param string $destination
	 * @param $context
	 * @return mixed
	 */
	public static function needToAsk(string $eventName, string $questionKey, string $destination, $context);
}