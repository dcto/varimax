<?php

namespace VM\Captcha;

/**
 * Class Captcha
 * @Version:1.2
 */
class Captcha {

    /**
     * 图片类型
     * @var array
     */
    protected $types = array('jpeg','png', 'gif');

    /**
     * 设置字体
     * @var array
     */
    protected $fonts;

    /**
     * 宽度
     * @var int
     */
    protected $width = 100;

    /**
     * 高度
     * @var int
     */
    protected $height = 30;


    /**
     * 过期时间(分钟)
     */
    protected $timeout = 2;

    /**
     * 字符数
     * @var
     */
    protected $length = 4;


    /**
     * code string
     * @var string
     */
    protected $string = '1234';

    /**
     * cookie名称
     * @var string
     */
    protected $cookie = 'captcha';

    /**
     * 图像
     * @var resource
     */
    protected $resource;
    
    

    public function __construct($width = 100, $height = 30, $length = 4, $fonts = null)
    {
        if (!extension_loaded("gd"))  throw new \ErrorException ("Captcha Unable Load GD Library");

        /**
         * Bold.otf 粗体
         * Camo.otf 超级干扰字体
         * False.otf 伪装小字体
         * Noise.otf 黑头干扰字体
         * Sans.otf 变形字体
         * Xed.otf X线干扰字体
         */
        $this->fonts = $fonts ?: array('ZXX/Bold.otf','ZXX/Noise.otf','ZXX/Sans.otf','ZXX/Xed.otf');
        $this->width = $width;
        $this->height = $height;
        $this->length = $length;
        $this->string = $this->code(4, 'ABCDEFGHIJKLMNPQRSTUVWXYZ123456789');
    }

    /**
     * verify Captcha and Str return true or false
     * @version 1.0
     * @param null $str
     * @return bool
     */
    public function is($input, $case = false)
    {
        $input = sprintf("%s", trim($input));
        $codes = make('cookie')->get($this->cookie);
        $codes = \Crypt::de($codes);
        make('cookie')->del($this->cookie);
        if(!$case && strtolower($input) === strtolower($codes)){
            return true;
        }else if($input === $codes){
            return true;
        }else{
            return false;
        }
    }


    /**
     * [make]
     *
     * @param int $width [宽度]
     * @param int $height [高度]
     * @param int $obstruct [干扰度]
     * @return mixed
     * @author 11.
     */
    public function make($width = 100, $height = 36, $disturb = 3)
    {
        /**
         * 设置宽度
         */
        $this->width = $width;

        /**
         * 设置高度
         */
        $this->height = $height;

        /**
         * 构建图形
         */
        $this->create($this->width, $this->height)->background()->disturb($disturb)->string();

        return $this;
    }

    /**
     * set width
     * @param $width
     */
    public function width($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * set height
     * @param $height
     * @return $this
     */
    public function height($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * get image
     *
     * @return mixed
     * @throws \ErrorException
     */
    private function resource()
    {
        if(is_resource($this->resource)){
            return $this->resource;
        }
        throw new \ErrorException('The image isn\' t an resource');
    }

    /**
     * [CreateCanvas 构建画布]
     *
     * @return $this
     * @author 11.
     */
    private function create($width = 100, $height = 36)
    {
        $this->width = $width;
        $this->height = $height;
        $this->resource = imagecreatetruecolor($this->width , $this->height);
        return $this;
    }


    /**
     * [setBackground 设置图片背景]
     *
     * @param $images
     * @return mixed
     * @author 11.
     */
    public function background($red = 0, $green = 0, $blue = 0)
    {
        $background = imagecolorallocate($this->resource, $red?:rand(155, 255), $green?:rand(155, 255), $blue?:rand(155, 255));
        imagefill($this->resource,0,0,$background);

        return $this;
    }


    /**
     * [setInterference 设置图片干扰]
     *
     * @param $images
     * @author 11.
     */
    public function disturb($level = 5)
    {
        for($i = 0; $i < $level; $i++)
        {
            $x = ($i*$this->width/4) + rand(5, 10);
            $y = $this->height / rand(1, 3);
            /**
             * 线条干扰
             */
            imageline($this->resource, rand(0,$this->width), rand(0,$this->height), rand($this->width/2,$this->width), rand($this->height/2, $this->height), $this->color('dark'));

            /**
             * 字符干扰
             */
            $codes = $this->code(rand($level, $level*2), '0123456789abcdefghijklmnopqrstuvwxyz');
            imagestring($this->resource, rand($i,10), $x, $y, $codes, $this->color('light'));
        }

        return $this;
    }

    /**
     * 验证码过期时间
     */
    public function timeout($minutes)
    {
        $this->time = $minutes;

        return $this;
    }

    /**
     * [setImageString 设置验证码]
     *
     * @param $images
     * @param $string
     * @return mixed
     * @author 11.
     */
    public function string($string = null)
    {
        $this->string = $string ?: $this->string;
        make('cookie')->set($this->cookie, \Crypt::en($this->string), $this->timeout);

        for ($i=0; $i< $this->length;$i++){
            $size = 18;
            $x = ($i*$this->width/4) + rand(5, 10);
            $y = $this->height / 2 + rand(5, $this->height / 2);
            $angle = $i + rand(-10, 30);
            imagefttext($this->resource, $size , $angle, $x, $y, $this->color('dark'), $this->fonts(), $this->string[$i]);

        }

        return $this;
    }

    /**
     * make color to resource
     *
     * @param $style
     * @return int
     */
    private function color($style)
    {
        switch($style){
            case 'light':
                return imagecolorallocate($this->resource, rand(133,255), rand(133,255), rand(133,255));
             break;

            case 'dark':
                return imagecolorallocate($this->resource, rand(0,120), rand(0,120), rand(0,120));
             break;

            default:
                throw new \InvalidArgumentException('The color style unable load');
        }
    }

    /**
     * [getFont 获取字体]
     *
     * @param null $font
     * @return string
     * @author 11.
     */
    private function fonts($font = null)
    {
        shuffle($this->fonts);
        $font = $font ?: $this->fonts[array_rand($this->fonts)];

        if(is_readable($font = __DIR__.'/Fonts/'.$font)){
            return $font;
        }

        throw new \ErrorException('The font '. $font .' unable load.');
    }


    /**
     * [getRandomCode 获取随机字符串]
     *
     * @param int  $length
     * @param null $code
     * @return string
     * @author 11.
     */
    public function code($length = 4, $code = null)
    {
        $code = is_null($code) ? array_merge(range(0,9),range('A','Z')) : (is_array($code)? $code : str_split($code) );
        shuffle($code);
        return implode(array_slice($code, 0, $length));
    }

    /**
     * alias render function
     */
    public function view($type)
    {
        return $this->image($type);
    }

    /**
     * alias render function
     *
     * @return mixed
     */
    public function image($type = null)
    {
        $type = $type ?: $this->types[array_rand($this->types)];
        header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header("content-type: image/" . $type);

        die($this->render($type));
    }

    /**
     * return base64 image
     *
     * @return string
     */
    public function base64($type = null)
    {
        $type = $type ?: $this->types[array_rand($this->types)];
        return 'data:image/' . $type . ';base64,' . base64_encode($this->render($type));
    }
    
    /**
     * @param null $type
     * @return string
     */
    public function render($type = null)
    {
        $type = $type ?: $this->types[array_rand($this->types)];
        $output =  'image'.$type;
        ob_start ();
        $output ($this->resource);
        $resource  = ob_get_contents ();
        ob_end_clean ();
        return $resource;
    }
}
