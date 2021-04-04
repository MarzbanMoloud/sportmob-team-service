<?php

namespace App\Services\Logger;

class Event implements SmLoggerInterface, RejectInterface, NeedToAskInterface
{
    use SmLoggerTrait;

    public static function received(string $eventName, ?string $param2 = null, $context)
    {
        self::logger()->alert(
            sprintf('Event "%s" received.', $eventName),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

    public static function rejected(string $eventName, string $reason, ?string $param3 = null, $context)
    {
        self::logger()->alert(
            sprintf('Event "%" rejected (%s)', $eventName, $reason),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

    public static function handled(string $eventName, string $handlerClassName, ?string $param3 = null, $context)
    {
        self::logger()->alert(
            sprintf('Event "%s" will handle by "%s".', $eventName, $handlerClassName),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

    public static function processing(string $eventName, $context)
    {
        self::logger()->alert(
            sprintf('"%s" handler in progress', $eventName),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

    public static function failed(string $eventName, string $reason, $context)
    {
        self::logger()->alert(
            sprintf('"%s" handler failed because of "%s"', $eventName, $reason),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

    public static function succeeded(string $eventName, $context)
    {
        self::logger()->alert(
            sprintf('"%s" handler completed successfully.', $eventName),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

    public static function needToAsk(string $eventName, string $questionKey, string $destination, $context)
    {
        self::logger()->alert(
            sprintf('"%s" handler needs to ask "%s" from "%s"', $eventName, $questionKey, $destination),
            is_object($context) ? self::serializer()->normalize($context, 'array') : $context,
        );
    }

}