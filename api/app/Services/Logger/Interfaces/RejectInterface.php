<?php

namespace App\Services\Logger\Interfaces;

/**
 * Interface RejectInterface
 * @package App\Services\Logger\Interfaces
 */
interface RejectInterface
{
	/**
	 * @param string $param1
	 * @param string $param2
	 * @param string|null $param3
	 * @param $context
	 * @return mixed
	 */
	public static function rejected(string $param1, string $param2, ?string $param3 = null, $context);
}