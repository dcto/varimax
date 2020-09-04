<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class Captcha
 * @method static bool is(string $input, $case = false) [判断验证码是否正确]
 * @method static VM\Captcha\Captcha make(int $width = 100, int $height = 30, int $obstruct = 5) 创建验证码
 * @method static VM\Captcha\Captcha code($length = 4, $code = null) 获取随机字符串
 * @method static VM\Captcha\Captcha string(string $string = null)
 * @method static VM\Captcha\Captcha disturb(int $level = 5) 设置图片干扰
 * @method static VM\Captcha\Captcha background(int $red = 0, int $green = 0, int $blue = 0) 设置图片背景
 * @method static VM\Captcha\Captcha width(int $width)
 * @method static VM\Captcha\Captcha height(int $height)
 * @method static string base64(string $type = null) Base64输出
 * @method static string view(string $type = null)  视图输出
 * @method static Resource render($type = null) 渲染输出
 */
class Captcha extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'captcha';
    }
}
