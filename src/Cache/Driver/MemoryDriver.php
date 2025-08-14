<?php
/**
 * Varimax The Slim PHP Frameworks.
 *
 * Github: https://github.com/dcto/varimax
 */

namespace VM\Cache\Driver;

use Swoole\Table;

/**
 * Class Swoole Table Driver
 *
 * @package VM\Cache\Driver
 *
 * @see https://www.php.net/manual/en/class.swoole-table.php
 */
class MemoryDriver extends Driver implements DriverInterface
{
    /**
     * @var Table
     */
    private $table;

    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @var int
     */
    private $size;

    /**
     * SwooleDriver constructor.
     * 
     * @param int $size Maximum number of rows (default: 1024)
     * @param string $prefix Cache key prefix
     */
    public function __construct($prefix = '', $size = 1024)
    {
        $this->size = $size;
        $this->prefix = $prefix;
        $this->initTable();
    }

    /**
     * Initialize Swoole Table
     */
    private function initTable()
    {
        $this->table = new Table($this->size);
        
        // Define table structure
        $this->table->column('value', \Swoole\Table::TYPE_STRING, 65536);  // Cache value (max 64KB)
        $this->table->column('expire', \Swoole\Table::TYPE_INT, 4);        // Expiration timestamp
        $this->table->column('type', \Swoole\Table::TYPE_STRING, 16);      // Data type for serialization
        
        $this->table->create();
    }

    /**
     * Get cache key with prefix
     *
     * @param string $key
     * @return string
     */
    private function getCacheKey($key)
    {
        return $this->prefix . $key;
    }

    /**
     * Check if cache item is expired
     *
     * @param array $item
     * @return bool
     */
    private function isExpired($item)
    {
        return $item && $item['expire'] > 0 && $item['expire'] < time();
    }

    /**
     * Clean up expired items
     */
    private function cleanExpired()
    {
        $currentTime = time();
        foreach ($this->table as $key => $item) {
            if ($item['expire'] > 0 && $item['expire'] < $currentTime) {
                $this->table->del($key);
            }
        }
    }

    /**
     * Serialize value for storage
     *
     * @param mixed $value
     * @return array [serialized_value, type]
     */
    private function serializeValue($value)
    {
        if (is_null($value)) {
            return [null, 'null'];
        } elseif (is_bool($value)) {
            return [$value ? '1' : '0', 'bool'];
        } elseif (is_int($value)) {
            return [(string)$value, 'int'];
        } elseif (is_float($value)) {
            return [(string)$value, 'float'];
        } elseif (is_string($value)) {
            return [$value, 'string'];
        } else {
            return [serialize($value), 'serialize'];
        }
    }

    /**
     * Unserialize value from storage
     *
     * @param string $value
     * @param string $type
     * @return mixed
     */
    private function unserializeValue($value, $type)
    {
        switch ($type) {
            case 'null':
                return null;
            case 'bool':
                return $value === '1';
            case 'int':
                return (int)$value;
            case 'float':
                return (float)$value;
            case 'string':
                return $value;
            case 'serialize':
                return unserialize($value);
            default:
                return $value;
        }
    }

    /**
     * Check an item from the cache by key.
     *
     * @param  string|array $key
     * @return bool
     */
    public function has($key)
    {
        $cacheKey = $this->getCacheKey($key);
        $item = $this->table->get($cacheKey);
        
        if (!$item) {
            return false;
        }
        
        if ($this->isExpired($item)) {
            $this->table->del($cacheKey);
            return false;
        }
        
        return true;
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $cacheKey = $this->getCacheKey($key);
        $item = $this->table->get($cacheKey);
        
        if (!$item) {
            return $default;
        }
        
        if ($this->isExpired($item)) {
            $this->table->del($cacheKey);
            return $default;
        }
        
        return $this->unserializeValue($item['value'], $item['type']);
    }

    /**
     * Store an item in the cache for a given number of seconds.
     *
     * @param  string $key
     * @param  mixed $value
     * @param  int $time Time in seconds (0 means no expiration)
     * @return bool
     */
    public function set($key, $value, $time = 86400)
    {
        $cacheKey = $this->getCacheKey($key);
        list($serializedValue, $type) = $this->serializeValue($value);
        
        $expire = $time > 0 ? time() + $time : 0;
        
        return $this->table->set($cacheKey, [
            'value' => $serializedValue,
            'expire' => $expire,
            'type' => $type
        ]);
    }

    /**
     * Retrieve multiple items from the cache by key.
     *
     * Items not found in the cache will have a null value.
     *
     * @param  array $keys
     * @return array
     */
    public function gets(array $keys)
    {
        $result = [];
        
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }
        
        return $result;
    }

    /**
     * Store multiple items in the cache for a given number of seconds.
     *
     * @param  array  $values
     * @param  int  $time
     * @return bool
     */
    public function sets(array $values, $time = 86400)
    {
        $success = true;
        
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $time)) {
                $success = false;
            }
        }
        
        return $success;
    }

    /**
     * Get key value automatic set value to key
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    public function cache($key, $value = null)
    {
        if (is_null($value)) {
            return $this->get($key);
        }
        
        if (is_callable($value)) {
            $cachedValue = $this->get($key);
            if ($cachedValue !== null) {
                return $cachedValue;
            }
            
            $computedValue = call_user_func($value);
            $this->set($key, $computedValue);
            return $computedValue;
        }
        
        $this->set($key, $value);
        return $value;
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string $key
     * @param  mixed $value
     * @return int|bool
     */
    public function increment($key, $value = 1)
    {
        $cacheKey = $this->getCacheKey($key);
        $item = $this->table->get($cacheKey);
        
        if (!$item || $this->isExpired($item)) {
            // If key doesn't exist, set it to the increment value
            $this->set($key, $value);
            return $value;
        }
        
        $currentValue = $this->unserializeValue($item['value'], $item['type']);
        
        if (!is_numeric($currentValue)) {
            return false;
        }
        
        $newValue = $currentValue + $value;
        $this->table->set($cacheKey, [
            'value' => (string)$newValue,
            'expire' => $item['expire'],
            'type' => is_int($newValue) ? 'int' : 'float'
        ]);
        
        return $newValue;
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  string $key
     * @param  mixed $value
     * @return int|bool
     */
    public function decrement($key, $value = 1)
    {
        return $this->increment($key, -$value);
    }

    /**
     * 持久化保存 (永久保存)
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return bool
     */
    public function save($key, $value)
    {
        return $this->set($key, $value, 0); // 0 means no expiration
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string $key
     * @return bool
     */
    public function del($key)
    {
        $cacheKey = $this->getCacheKey($key);
        return $this->table->del($cacheKey);
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {
        // Swoole Table doesn't have a direct flush method
        // We need to iterate and delete all keys
        foreach ($this->table as $key => $item) {
            $this->table->del($key);
        }
        
        return true;
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function prefix()
    {
        return $this->prefix;
    }

    /**
     * Get table statistics
     *
     * @return array
     */
    public function stats()
    {
        return [
            'size' => $this->size,
            'count' => $this->table->count(),
            'memory_usage' => $this->table->memorySize ?? 'unknown'
        ];
    }

    /**
     * Manual cleanup of expired items
     *
     * @return int Number of cleaned items
     */
    public function gc()
    {
        $cleanedCount = 0;
        $currentTime = time();
        
        foreach ($this->table as $key => $item) {
            if ($item['expire'] > 0 && $item['expire'] < $currentTime) {
                $this->table->del($key);
                $cleanedCount++;
            }
        }
        
        return $cleanedCount;
    }

    /**
     * Get all cache keys
     *
     * @return array
     */
    public function keys()
    {
        $keys = [];
        $prefixLength = strlen($this->prefix);
        
        foreach ($this->table as $key => $item) {
            if (!$this->isExpired($item)) {
                // Remove prefix from key if it exists
                $keys[] = $prefixLength > 0 ? substr($key, $prefixLength) : $key;
            }
        }
        
        return $keys;
    }
}