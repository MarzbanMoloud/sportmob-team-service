<?php

namespace App\Services\Logger;

interface NeedToAskInterface
{
    public static function needToAsk(string $eventName, string $questionKey, string $destination, $context);
}