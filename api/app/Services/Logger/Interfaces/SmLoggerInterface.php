<?php

namespace App\Services\Logger;

interface SmLoggerInterface
{
    public static function received(string $eventName, ?string $source, $context);

    public static function handled(string $eventName, string $source, string $className, $context);

    public static function processing(string $eventName, $context);

    public static function failed(string $eventName, string $source, $context);

    public static function succeeded(string $eventName, $context);
}