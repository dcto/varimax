<?php

namespace VM\Crypt\Driver;

/**
 *
 * 本类按照RC4加密解密算法编写
 * @author 11
 * @version 20161222
 * @link http://en.wikipedia.org/wiki/RC4
 */
class Rc4 extends CryptDriver
{
    /**
     * 密钥
     * @var string
     */
    private $key;

    /**
     * Crypt constructor.
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * 加密
     * @param string $key 私匙
     * @param string $data 需要加密的数据
     * @param boolean $decrypted 是否解密
     * @return 16进制字符串
     */
    public function en($string, $key = false, $decrypted = false)
    {
        $string = (string) $string;
        $keyLength = strlen($key = $key ?: $this->key);
        $s = array();
        for($i = 0; $i < 256; $i++) $s[$i] = $i;
        $j = 0;
        for ($i = 0; $i < 256; $i++)
        {
            $j = ($j + $s[$i] + ord($key[$i % $keyLength])) % 256;
            $this->swap($s[$i], $s[$j]);
        }

        $stringLength = strlen($string);
        $output = "";
        for ($a = $j = $i = 0; $i < $stringLength; $i++)
        {
            $a = ($a + 1) % 256;
            $j = ($j + $s[$a]) % 256;
            $this->swap($s[$a], $s[$j]);
            $k = $s[(($s[$a] + $s[$j]) % 256)];
            $output .= chr(ord($string[$i]) ^ $k);
        }

        return ($decrypted) ? $output : bin2hex($output);
    }
    /**
     * 解密
     * @param string $a 私匙
     * @param string $b 需要解密的数据
     * @return string
     */
    public function de($string, $key = false)
    {
        $string = (string) $string;
        $stringLength = strlen($string);
        if($stringLength % 2){
            return $string;
        }else if (strspn($string , '0123456789abcdef' ) != $stringLength){
            return $string;
        }
        $key = $key?:$this->key;
        if (function_exists("hex2bin")){
            return $this->en(hex2bin($string), $key,  true);
        }else{//由于hex2bin php5.4才支持采用pack方式处理
            return $this->en(pack("H*", $string), $key, true);
        }
    }

    /**
     * 临时缓冲
     * @param $key
     * @param $string
     */
    private function swap(&$key, &$string)
    {
        $swap = $key;
        $key = $string;
        $string = $swap;
    }
}