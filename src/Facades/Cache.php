<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class Cache
 *
 * @method static bool has(string $key)
 * @method static mixed get(string $key)
 * @method static \VM\Cache\Driver set(string $key, mixed $value, int $time = 86400)
 * @method static array gets(array $key)
 * @method static bool sets(array $values, $time = 86400)
 * @method static \VM\Cache\Driver increment(string $key, int $value = 1, int $time = 86400)
 * @method static \VM\Cache\Driver decrement(string $key, int $value = 1)
 * @method static bool save(string $key, mixed $value)
 * @method static bool del(string $key)
 * @method static bool flush()
 * @method static mixed prefix($prefix = false)
 * @method static \VM\Cache\Driver\ApcDriver apc(string $file = 'default')
 * @method static \VM\Cache\Driver\FilesDriver file(string $file = 'default')
 * @method static \VM\Cache\Driver\RedisDriver|\Redis redis(string $server = 'default');
 */
class Cache extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cache';
    }
}
