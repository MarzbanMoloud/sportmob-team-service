<?php

namespace App\Services\Logger;

use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

trait SmLoggerTrait
{
    public static function logger(): LoggerInterface
    {
        return app(LoggerInterface::class);
    }

    public static function serializer(): SerializerInterface
    {
        return app(SerializerInterface::class);
    }
}