<?php

namespace App\Services\Logger;

class Answer implements SmLoggerInterface, RejectInterface
{
    use SmLoggerTrait;

    public static function received(string $answerKey, ?string $source = null, $context)
    {
        self::logger()->alert(
            sprintf('Answer  "%s" by "%s" received.', $answerKey, $source),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

    public static function rejected(string $answerKey, string $source, ?string $reason = null, $context)
    {
        self::logger()->alert(
            sprintf('Answer "%s" by "%s" rejected (%s).', $answerKey, $source, $reason),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

    public static function handled(string $answerKey, string $source, ?string $handlerClassName = null, $context)
    {
        self::logger()->alert(
            sprintf('Answer "%s" by "%s" will handle by "%s".', $answerKey, $source, $handlerClassName),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

    public static function processing(string $answerKey, $context)
    {
        self::logger()->alert(
            sprintf('"%s" handler in progress.', $answerKey),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

    public static function failed(string $answerKey, string $reason, $context)
    {
        self::logger()->alert(
            sprintf('"%s" handler failed because of "%s".', $answerKey, $reason),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

    public static function succeeded(string $answerKey, $context)
    {
        self::logger()->alert(
            sprintf('"%s" handler completed successfully.', $answerKey),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }
}