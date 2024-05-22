<?php
/**
 * Varimax The Slim PHP Frameworks.
 *

 * Time: 2024-05-10 15:30:00
 * Github: https://github.com/dcto/varimax
 */
namespace VM;

class Context
{
    /**
     * The pool of variables in the current coroutine context.
     */
    protected static $pool = [];

    /**
     * Get the value of a variable in the current coroutine context.
     */
    static function get($key)
    {
        if($cid = coid() < 0) return null;
        return self::$pool[$cid][$key] ?? null;
    }

    /**
     * Set the value of a variable in the current coroutine context.
     */
    static function put($key, $value)
    {
        if($cid = coid() > 0) self::$pool[$cid][$key] = $value;
    }
    
    /**
     * Delete a variable in the current coroutine context.
     */
    static function delete($key = null)
    {
        if($cid = coid() > 0){
            if(is_null($key)) {
                unset(self::$pool[$cid]);
            }else{
                unset(self::$pool[$cid][$key]);
            }
        }
    }
}