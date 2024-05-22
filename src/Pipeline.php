<?php
/**
 * Varimax The Slim PHP Frameworks.
 *
 */

namespace VM;

/**
 * @package Pipeline
 */
class Pipeline extends \Illuminate\Pipeline\Pipeline
{
    /**
     * Handles the value returned from each pipe before passing it to the next.
     *
     * @param  mixed  $carry
     * @return mixed
     */
    protected function handleCarry($carry)
    {
        return $carry;
    }

    /**
     * Handle the given exception.
     *
     * @param  mixed  $passable
     * @param  \Throwable  $e
     * @return mixed
     *
     * @throws \Throwable
     */
    protected function handleException($passable, \Throwable $e)
    {
        throw $e;
    }
}