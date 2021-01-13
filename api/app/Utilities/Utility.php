<?php


namespace App\Utilities;


/**
 * Class Utility
 * @package App\Utilities
 */
class Utility
{
    /**
     * @param array $data
     * @return string
     */
    public static function jsonEncode(array $data): string
    {
        return base64_encode(json_encode($data));
    }

    /**
     * @param string $data
     * @return array
     */
    public static function jsonDecode(string $data): array
    {
        return json_decode(base64_decode($data), true);
    }
}
