<?php

namespace VM\Cache\Driver;

trait RetrievesMultipleKeys
{
    /**
     * Retrieve multiple items from the cache by key.
     *
     * Items not found in the cache will have a null value.
     *
     * @param  array  $keys
     * @return array
     */
    public function gets(array $keys)
    {
        $return = [];

        foreach ($keys as $key) {
            $return[$key] = $this->get($key);
        }

        return $return;
    }

    /**
     * Store multiple items in the cache for a given number of minutes.
     *
     * @param  array  $values
     * @param  int  $time
     * @return void
     */
    public function sets(array $values, $time = 86400)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $time);
        }
    }
}
