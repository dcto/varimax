<?php

/**
 * Varimax The Full Stack PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 21:49
 * SITE: https://www.varimax.cn/
 */


namespace VM\Crypt;

use VM\Crypt\Driver\Rc4;
use VM\Crypt\Driver\CryptDriver;

class Crypt
{
    /**
     * Crypt private key
     *
     * @var string
     */
    private $key;

    /**
     * Crypt driver
     *
     * @var CryptDriver
     */
    private $driver = null;


    /**
     * Crypt constructor.
     * @param null $key
     */
    public function __construct()
    {
        $this->key = config('app.key');
    }

    /**
     * $key
     *
     * @param null $key
     * @return null|string
     */
    public function key($key = null)
    {
        if($key){
            $this->key = $key;
            return $this;
        }else{
            return $this->key;
        }
    }


    /**
     * 加密方式加载
     * @param $driver
     * @return CryptDriver
     */
    public function driver($driver = null)
    {
        if(!$driver){
            return $this->Rc4();
        }else{
            if(method_exists($this, $driver)){
                return $this->$driver();
            }else{
                throw new \InvalidArgumentException('Unable load crypt driver');
            }
        }
    }

    /**
     * the default crypt rc4
     *
     * @return CryptDriver
     */
    public function Rc4(){
       if($this->driver instanceof Rc4) {
           return $this->driver;
       }
        return $this->driver = new Rc4($this->key);
    }

    /**
     * [__call]
     *
     * @param       $method
     * @param array $parameters
     * @return $this->driver()
     * @author 11.
     */
    public function __call($method, array $parameters = [])
    {
        return call_user_func_array([$this->driver(), $method], $parameters);
    }
}