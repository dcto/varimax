<?php

/**
 * Varimax The Full Stack PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 21:49
 * SITE: https://www.varimax.cn/
 */

namespace VM\FileSystem;

use Symfony\Component\Finder\SplFileInfo;

class FileSystem extends \Illuminate\Filesystem\Filesystem
{


    /**
     * 判断文件是否存在
     * @param $path
     * @return bool
     */
    public function has($path)
    {
        return $this->exists($path);
    }


    /**
     * 删除
     * @param $path
     * @return bool
     */
    public function del($path)
    {
        return $this->delete($path);
    }

    /**
     * 写入文件内容
     * @param $path
     * @param $content
     * @param bool $lock
     * @return int
     */
    public function set($path, $content, $lock = false)
    {
        return $this->put($path, $content, $lock);
    }


    /**
     * 递归创建文件目录
     *
     * @param $path
     * @param int $mode
     * @param bool $recursive
     * @param bool $force
     * @return bool
     */
    public function mkDir($path, $mode = 0755, $recursive = true, $force = false)
    {
        return $this->makeDirectory($path, $mode, $recursive, $force);
    }

    /**
     * 判断是否是目录
     * @param $path
     * @return bool
     */
    public function isDir($path)
    {
        return $this->isDirectory($path);
    }

    /**
     * 拷贝目录
     * @param $dir
     * @param $target
     * @param null $options
     * @return bool
     */
    public function copyDir($dir, $target, $options = null)
    {
        return $this->copyDirectory($dir, $target, $options);
    }


    /**
     * @param string $directory
     * @param bool $hidden
     * @return array
     */
    public function files($directory, $hidden = false)
    {
        if(self::isDir($directory)){
            return $this->allFiles($directory, $hidden);
        }else if($glob = glob($directory)){
            $files = array();
            foreach($glob as $file){
                $files[] = new SplFileInfo($file, pathinfo($file, PATHINFO_DIRNAME), pathinfo($file,PATHINFO_FILENAME));
            }
            return $files;
        }else{
            return array();
        }
    }
}