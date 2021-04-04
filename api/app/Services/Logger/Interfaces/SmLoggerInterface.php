<?php

namespace App\Services\Logger\Interfaces;

/**
 * Interface SmLoggerInterface
 * @package App\Services\Logger\Interfaces
 */
interface SmLoggerInterface
{
	/**
	 * @param string $eventName
	 * @param string|null $source
	 * @param $context
	 * @return mixed
	 */
	public static function received(string $eventName, ?string $source, $context);

	/**
	 * @param string $eventName
	 * @param string $source
	 * @param string $className
	 * @param $context
	 * @return mixed
	 */
	public static function handled(string $eventName, string $source, string $className, $context);

	/**
	 * @param string $eventName
	 * @param $context
	 * @return mixed
	 */
	public static function processing(string $eventName, $context);

	/**
	 * @param string $eventName
	 * @param string $source
	 * @param $context
	 * @return mixed
	 */
	public static function failed(string $eventName, string $source, $context);

	/**
	 * @param string $eventName
	 * @param $context
	 * @return mixed
	 */
	public static function succeeded(string $eventName, $context);
}