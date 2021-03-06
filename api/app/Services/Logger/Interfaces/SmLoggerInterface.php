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
	public static function received($context, string $eventName, ?string $source);

    /**
     * @param string $param1
     * @param string $param2
     * @param string|null $param3
     * @param $context
     * @return mixed
     */
    public static function rejected($context, string $param1, string $param2, ?string $param3 = null);

	/**
	 * @param string $eventName
	 * @param string $source
	 * @param string $className
	 * @param $context
	 * @return mixed
	 */
	public static function handled($context, string $eventName, string $source, string $className);

	/**
	 * @param string $eventName
	 * @param $context
	 * @return mixed
	 */
	public static function processing($context, string $eventName);

	/**
	 * @param string $eventName
	 * @param string $source
	 * @param $context
	 * @return mixed
	 */
	public static function failed($context, string $eventName, string $source);

	/**
	 * @param string $eventName
	 * @param $context
	 * @return mixed
	 */
	public static function succeeded($context, string $eventName);
}