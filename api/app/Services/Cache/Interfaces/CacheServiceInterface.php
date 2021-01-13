<?php


namespace App\Services\Cache\Interfaces;


/**
 * Interface CacheServiceInterface
 * @package App\Services\Cache\Interfaces
 */
interface CacheServiceInterface
{
    const DAY = 86400;
    const WEEK = 604800;
    const HOUR = 3600;
    const MINUTE = 60;

    /**
     * @param $function
     * @param string $key
     * @param int $ttl
     * @return mixed
     */
    public function remember($function, string $key, $ttl = CacheServiceInterface::DAY);

    /**
     * @param string $key
     * @param $function
     * @return mixed
     */
    public function rememberForever(string $key, $function);

    /**
     * @param $function
     * @param string $key
     * @param int $ttl
     * @param array $tags
     * @return mixed
     */
    public function rememberByTags($function, string $key, int $ttl , array $tags);

    /**
     * @param string $key
     * @return mixed
     */
    public function forget(string $key);

    /**
     * @param array $tags
     * @return mixed
     */
    public function forgetByTags(array $tags);

    /**
     * @param string $key
     * @param $value
     * @return mixed
     */
    public function put(string $key, $value);

    /**
     * @param string $key
     * @param $value
     * @param int $ttl
     * @param array $tags
     * @return mixed
     */
    public function putByTags(string $key, $value, int $ttl, array $tags);

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key);

    /**
     * @param string $key
     * @param array $tags
     * @return mixed
     */
    public function getByTags(string $key, array $tags);

    /**
     * @param string $key
     * @return mixed
     */
    public function hasKey(string $key);

    /**
     * @param string $key
     * @param array $tags
     * @return mixed
     */
    public function hasByTags(string $key, array $tags);

    /**
     * @return mixed
     */
    public function flush();
}
