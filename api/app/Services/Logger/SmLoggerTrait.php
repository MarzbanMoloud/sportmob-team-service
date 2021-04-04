<?php

namespace App\Services\Logger;

use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Trait SmLoggerTrait
 * @package App\Services\Logger
 */
trait SmLoggerTrait
{
	/**
	 * @return LoggerInterface
	 */
	public static function logger(): LoggerInterface
    {
        return app(LoggerInterface::class);
    }

	/**
	 * @return SerializerInterface
	 */
	public static function serializer(): SerializerInterface
    {
        return app(SerializerInterface::class);
    }
}