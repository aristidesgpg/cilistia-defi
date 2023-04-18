<?php

namespace App\Models\Support;

trait StaticCache
{
    /**
     * Cache store
     *
     * @var array
     */
    protected static array $staticCacheStore = [];

    /**
     * Cache value
     *
     * @param  string  $key
     * @param  callable  $callback
     * @return mixed
     */
    protected static function staticRemember(string $key, callable $callback): mixed
    {
        if (!array_key_exists($key, static::$staticCacheStore)) {
            static::$staticCacheStore[$key] = $callback();
        }

        return static::$staticCacheStore[$key];
    }
}
