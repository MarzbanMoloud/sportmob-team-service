<?php


namespace App\Utilities;


/**
 * Class Utility
 * @package App\Utilities
 */
class Utility
{
    /**
     * @param array $lastEvaluatedKey
     * @return string
     */
    public static function jsonEncode(array $lastEvaluatedKey): string
    {
        return base64_encode(json_encode($lastEvaluatedKey));
    }

    /**
     * @param string $lastEvaluatedKey
     * @return array
     */
    public static function jsonDecode(string $lastEvaluatedKey): array
    {
        return json_decode(base64_decode($lastEvaluatedKey), true);
    }
}
