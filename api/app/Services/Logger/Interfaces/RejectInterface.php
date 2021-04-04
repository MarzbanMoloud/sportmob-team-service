<?php

namespace App\Services\Logger;

interface RejectInterface
{
    public static function rejected(string $param1, string $param2, ?string $param3 = null, $context);
}