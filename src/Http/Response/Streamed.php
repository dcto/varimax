<?php

/**
 * Varimax The Full Stack PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 22:03
 * SITE: https://www.varimax.cn/
 */


namespace VM\Http\Response;

use Symfony\Component\HttpFoundation\StreamedResponse;

class Streamed extends StreamedResponse implements ResponseInterface
{
    use ResponseTrait;
    
}