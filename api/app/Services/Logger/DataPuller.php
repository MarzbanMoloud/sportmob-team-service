<?php

namespace App\Services\Logger;

use App\Services\Logger\Interfaces\DispatchInterface;
use App\Services\Logger\Interfaces\SmLoggerInterface;

/**
 * Class DataPuller
 * @package App\Services\Logger
 */
class DataPuller implements SmLoggerInterface, DispatchInterface
{
    use SmLoggerTrait;

	/**
	 * @param string $messageName
	 * @param string|null $param2
	 * @param $context
	 * @return mixed|void
	 */
	public static function received($context, string $messageName, ?string $param2 = null)
    {
        self::logger()->alert(
            sprintf('Message "%s" received from the data puller.', $messageName),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

    /**
     * @param $context
     * @param string $param1
     * @param string $param2
     * @param string|null $param3
     * @return mixed|void
     */
    public static function rejected($context, string $eventName, ?string $reason = 'there is no strategy', ?string $param3 = null)
    {
        self::logger()->alert(
            sprintf('Message "%" rejected (%s)', $eventName, $reason),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

	/**
	 * @param string $messageName
	 * @param string $handlerClassName
	 * @param string|null $param3
	 * @param $context
	 * @return mixed|void
	 */
	public static function handled($context, string $messageName, string $handlerClassName, ?string $param3 = null)
    {
        self::logger()->alert(
            sprintf('Message "%s" will handle by "%s".', $messageName, $handlerClassName),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

	/**
	 * @param string $messageName
	 * @param $context
	 * @return mixed|void
	 */
	public static function processing($context, string $messageName)
    {
        self::logger()->alert(
            sprintf('"%s" handler in progress.', $messageName),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

	/**
	 * @param string $messageName
	 * @param string $reason
	 * @param $context
	 * @return mixed|void
	 */
	public static function failed($context, string $handler, string $reason)
    {
        self::logger()->alert(
            sprintf('"%s" handler failed because "%s".', $handler, $reason),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

	/**
	 * @param string $messageName
	 * @param $context
	 * @return mixed|void
	 */
	public static function succeeded($context, string $messageName)
    {
        self::logger()->alert(
            sprintf('"%s" handler completed successfully.', $messageName),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

    /**
     * @param mixed $context
     * @param string $eventName
     * @return mixed|void
     */
    public static function dispatched($context, string $eventName)
    {
        self::logger()->alert(
            sprintf('Event "%"  was dispatched', $eventName),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }
}