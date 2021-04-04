<?php

namespace App\Services\Logger;

use App\Services\Logger\Interfaces\SmLoggerInterface;

/**
 * Class DataPuller
 * @package App\Services\Logger
 */
class DataPuller implements SmLoggerInterface
{
    use SmLoggerTrait;

	/**
	 * @param string $messageName
	 * @param string|null $param2
	 * @param $context
	 * @return mixed|void
	 */
	public static function received(string $messageName, ?string $param2 , $context)
    {
        self::logger()->alert(
            sprintf('Message "%s" received from the data puller.', $messageName),
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
	public static function handled(string $messageName, string $handlerClassName, ?string $param3, $context)
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
	public static function processing(string $messageName, $context)
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
	public static function failed(string $messageName, string $reason, $context)
    {
        self::logger()->alert(
            sprintf('%s" handler failed because of "%s".', $messageName, $reason),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

	/**
	 * @param string $messageName
	 * @param $context
	 * @return mixed|void
	 */
	public static function succeeded(string $messageName, $context)
    {
        self::logger()->alert(
            sprintf('"%s" handler completed successfully.', $messageName),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }
}