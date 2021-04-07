<?php

namespace App\Services\Logger;

use App\Services\Logger\Interfaces\RejectInterface;
use App\Services\Logger\Interfaces\SmLoggerInterface;

/**
 * Class Answer
 * @package App\Services\Logger
 */
class Answer implements SmLoggerInterface, RejectInterface
{
    use SmLoggerTrait;

	/**
	 * @param string $answerKey
	 * @param string|null $source
	 * @param $context
	 * @return mixed|void
	 */
	public static function received($context, string $answerKey, ?string $source = null)
    {
        self::logger()->alert(
            sprintf('Answer  "%s" by "%s" received.', $answerKey, $source),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

	/**
	 * @param string $answerKey
	 * @param string $source
	 * @param string|null $reason
	 * @param $context
	 * @return mixed|void
	 */
	public static function rejected($context, string $answerKey, string $source, ?string $reason = null)
    {
        self::logger()->alert(
            sprintf('Answer "%s" by "%s" rejected (%s).', $answerKey, $source, $reason),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

	/**
	 * @param string $answerKey
	 * @param string $source
	 * @param string|null $handlerClassName
	 * @param $context
	 * @return mixed|void
	 */
	public static function handled($context, string $answerKey, string $source, ?string $handlerClassName = null)
    {
        self::logger()->alert(
            sprintf('Answer "%s" by "%s" will handle by "%s".', $answerKey, $source, $handlerClassName),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

	/**
	 * @param string $answerKey
	 * @param $context
	 * @return mixed|void
	 */
	public static function processing($context, string $answerKey)
    {
        self::logger()->alert(
            sprintf('"%s" handler in progress.', $answerKey),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

	/**
	 * @param string $answerKey
	 * @param string $reason
	 * @param $context
	 * @return mixed|void
	 */
	public static function failed($context, string $answerKey, string $reason)
    {
        self::logger()->alert(
            sprintf('"%s" handler failed because of "%s".', $answerKey, $reason),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

	/**
	 * @param string $answerKey
	 * @param $context
	 * @return mixed|void
	 */
	public static function succeeded($context, string $answerKey)
    {
        self::logger()->alert(
            sprintf('"%s" handler completed successfully.', $answerKey),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }
}