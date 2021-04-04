<?php

namespace App\Services\Logger;

use App\Services\Logger\Interfaces\NeedToAskInterface;
use App\Services\Logger\Interfaces\RejectInterface;
use App\Services\Logger\Interfaces\SmLoggerInterface;

/**
 * Class Event
 * @package App\Services\Logger
 */
class Event implements SmLoggerInterface, RejectInterface, NeedToAskInterface
{
    use SmLoggerTrait;

	/**
	 * @param string $eventName
	 * @param string|null $param2
	 * @param $context
	 * @return mixed|void
	 */
	public static function received(string $eventName, ?string $param2 = null, $context)
    {
        self::logger()->alert(
            sprintf('Event "%s" received.', $eventName),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

	/**
	 * @param string $eventName
	 * @param string $reason
	 * @param string|null $param3
	 * @param $context
	 * @return mixed|void
	 */
	public static function rejected(string $eventName, string $reason, ?string $param3 = null, $context)
    {
        self::logger()->alert(
            sprintf('Event "%" rejected (%s)', $eventName, $reason),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

	/**
	 * @param string $eventName
	 * @param string $handlerClassName
	 * @param string|null $param3
	 * @param $context
	 * @return mixed|void
	 */
	public static function handled(string $eventName, string $handlerClassName, ?string $param3 = null, $context)
    {
        self::logger()->alert(
            sprintf('Event "%s" will handle by "%s".', $eventName, $handlerClassName),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

	/**
	 * @param string $eventName
	 * @param $context
	 * @return mixed|void
	 */
	public static function processing(string $eventName, $context)
    {
        self::logger()->alert(
            sprintf('"%s" handler in progress', $eventName),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

	/**
	 * @param string $eventName
	 * @param string $reason
	 * @param $context
	 * @return mixed|void
	 */
	public static function failed(string $eventName, string $reason, $context)
    {
        self::logger()->alert(
            sprintf('"%s" handler failed because of "%s"', $eventName, $reason),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

	/**
	 * @param string $eventName
	 * @param $context
	 * @return mixed|void
	 */
	public static function succeeded(string $eventName, $context)
    {
        self::logger()->alert(
            sprintf('"%s" handler completed successfully.', $eventName),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

	/**
	 * @param string $eventName
	 * @param string $questionKey
	 * @param string $destination
	 * @param $context
	 * @return mixed|void
	 */
	public static function needToAsk(string $eventName, string $questionKey, string $destination, $context)
    {
        self::logger()->alert(
            sprintf('"%s" handler needs to ask "%s" from "%s"', $eventName, $questionKey, $destination),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

}