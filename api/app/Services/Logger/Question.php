<?php

namespace App\Services\Logger;

class Question implements SmLoggerInterface, RejectInterface
{
    use SmLoggerTrait;

    public static function received(string $questionKey, ?string $source = null, $context)
    {
        self::logger()->alert(
            sprintf('Question "%s" by "%s" received.', $questionKey, $source),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

    public static function rejected(string $questionKey, string $source, ?string $reason = null, $context)
    {
        self::logger()->alert(
            sprintf('Question "%s" by "%s" rejected (%s).', $questionKey, $source, $reason),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

    public static function handled(string $questionKey, string $source, ?string $handlerClassName = null, $context)
    {
        self::logger()->alert(
            sprintf('Question "%s" by "%s" will handle by "%s".', $questionKey, $source, $handlerClassName),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

    public static function processing(string $questionKey, $context)
    {
        self::logger()->alert(
            sprintf('"%s" handler in progress.', $questionKey),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

    public static function failed(string $questionKey, string $reason, $context)
    {
        self::logger()->alert(
            sprintf('"%s" handler failed because of "%s".', $questionKey, $reason),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

    public static function succeeded(string $questionKey, $context)
    {
        self::logger()->alert(
            sprintf('"%s" handler completed successfully.', $questionKey),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }
}