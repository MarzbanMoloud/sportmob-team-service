<?php


namespace App\Services\Cache;


use App\Services\Cache\Interfaces\CacheServiceInterface;


/**
 * Class CacheService
 * @package App\Services\Cache
 */
class CacheService implements CacheServiceInterface
{
    /**
     * @param $function
     * @param string $key
     * @param int $ttl
     * @return mixed
     */
    public function remember($function, string $key, $ttl = CacheServiceInterface::DAY)
    {
        return app()->make('cache')->remember($key, $ttl, $function);
    }

    /**
     * @param string $key
     * @param $function
     * @return mixed
     */
    public function rememberForever(string $key, $function)
    {
        return app()->make('cache')->rememberForever($key, $function);
    }

    /**
     * @param $function
     * @param string $key
     * @param int $ttl
     * @param array $tags
     * @return array|mixed
     */
    public function rememberByTags($function, string $key, int $ttl, array $tags)
    {
        return app()->make('cache')->tags($tags)->remember($key, $ttl, $function);
    }

    /**
     * @param string $key
     * @return mixed|void
     */
    public function forget(string $key)
    {
        $redis = app()->make('redis');
        $cachePrefix = app()->make('cache')->getPrefix();
        $cacheKeyPattern = $cachePrefix . $key;
        $clientListCacheKeys = $redis->keys($cacheKeyPattern);
        if ($clientListCacheKeys) {
            $redis->del($clientListCacheKeys);
        }

        app()->make('cache')->forget($key);
    }

    /**
     * @param array $tags
     * @return mixed|void
     */
    public function forgetByTags(array $tags)
    {
        app()->make('cache')->tags($tags)->flush();
    }

    /**
     * @param string $key
     * @param $value
     * @return mixed|void
     */
    public function put(string $key, $value)
    {
        app()->make('cache')->put($key, $value);
    }

    /**
     * @param string $key
     * @param $value
     * @param int $ttl
     * @param array $tags
     * @return mixed|void
     */
    public function putByTags(string $key, $value, int $ttl, array $tags)
    {
        app()->make('cache')->tags($tags)->put($key, $value, $ttl);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return app()->make('cache')->get($key);
    }

    /**
     * @param string $key
     * @param array $tags
     * @return array|mixed
     */
    public function getByTags(string $key, array $tags)
    {
        return app()->make('cache')->tags($tags)->get($key);
    }

    /**
     * @param string $key
     * @return bool|mixed
     */
    public function hasKey(string $key)
    {
        if (app()->make('cache')->has($key)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $key
     * @param array $tags
     * @return bool|mixed
     */
    public function hasByTags(string $key, array $tags)
    {
        if (app()->make('cache')->tags($tags)->has($key)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return mixed|void
     */
    public function flush()
    {
        app()->make('cache')->flush();
    }
}
