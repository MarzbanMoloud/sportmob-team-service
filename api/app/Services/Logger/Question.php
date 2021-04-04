<?php

namespace App\Services\Logger;

use App\Services\Logger\Interfaces\RejectInterface;
use App\Services\Logger\Interfaces\SmLoggerInterface;

/**
 * Class Question
 * @package App\Services\Logger
 */
class Question implements SmLoggerInterface, RejectInterface
{
    use SmLoggerTrait;

	/**
	 * @param string $questionKey
	 * @param string|null $source
	 * @param $context
	 * @return mixed|void
	 */
	public static function received(string $questionKey, ?string $source = null, $context)
    {
        self::logger()->alert(
            sprintf('Question "%s" by "%s" received.', $questionKey, $source),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

	/**
	 * @param string $questionKey
	 * @param string $source
	 * @param string|null $reason
	 * @param $context
	 * @return mixed|void
	 */
	public static function rejected(string $questionKey, string $source, ?string $reason = null, $context)
    {
        self::logger()->alert(
            sprintf('Question "%s" by "%s" rejected (%s).', $questionKey, $source, $reason),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

	/**
	 * @param string $questionKey
	 * @param string $source
	 * @param string|null $handlerClassName
	 * @param $context
	 * @return mixed|void
	 */
	public static function handled(string $questionKey, string $source, ?string $handlerClassName = null, $context)
    {
        self::logger()->alert(
            sprintf('Question "%s" by "%s" will handle by "%s".', $questionKey, $source, $handlerClassName),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

	/**
	 * @param string $questionKey
	 * @param $context
	 * @return mixed|void
	 */
	public static function processing(string $questionKey, $context)
    {
        self::logger()->alert(
            sprintf('"%s" handler in progress.', $questionKey),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

	/**
	 * @param string $questionKey
	 * @param string $reason
	 * @param $context
	 * @return mixed|void
	 */
	public static function failed(string $questionKey, string $reason, $context)
    {
        self::logger()->alert(
            sprintf('"%s" handler failed because of "%s".', $questionKey, $reason),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

	/**
	 * @param string $questionKey
	 * @param $context
	 * @return mixed|void
	 */
	public static function succeeded(string $questionKey, $context)
    {
        self::logger()->alert(
            sprintf('"%s" handler completed successfully.', $questionKey),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }
}