<?php

namespace VM\Crypt\Driver;


abstract class CryptDriver
{

    /**
     * Encrypt String
     *
     * @param $string
     */
    public function en($string){}


    /**
     * Decrypt String
     *
     * @param $string
     */
    public function de($string){}

}