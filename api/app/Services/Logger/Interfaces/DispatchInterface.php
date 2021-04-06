<?php

namespace App\Services\Logger\Interfaces;

interface DispatchInterface
{
    /**
     * @param mixed $context
     * @param string $eventName
     * @return mixed
     */
    public static function dispatched($context, string $eventName);
}