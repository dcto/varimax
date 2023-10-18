<?php
/**
 * Varimax The Slim PHP Frameworks.
 * varimax
 * FILE: 2020
 * USER: 陶之11. <sdoz@live.com>
 * Time: 2020-08-11 22:03
 * SITE: https://www.varimax.cn/
 */


namespace VM\Http\Request;


use Symfony\Component\HttpFoundation\File\UploadedFile;

class Upload extends UploadedFile
{

    /**
     * @var
     */
    protected $name;

    /**
     * @var
     */
    protected $filename;

    /**
     * get upload status
     *
     * @return bool
     */
    public function ok()
    {
        return $this->isValid();
    }


    /**
     * Get the fully qualified path to the file.
     *
     * @return string
     */
    public function path()
    {
        return $this->getRealPath();
    }

    /**
     * @return string
     */
    public function filename()
    {
        return $this->getFilename();
    }

    /**
     * Get the file's extension.
     *
     * @return string
     */
    public function extension()
    {
        return $this->guessExtension();
    }

    /**
     * Get the file's extension supplied by the client.
     *
     * @return string
     */
    public function ext()
    {
        return $this->getClientOriginalExtension();
    }

    /**
     * Get file md5 hash
     *
     * @param null $path
     * @return string
     */
    public function md5()
    {
        return md5_file($this->path());
    }


    /**
     * Returns the extension based on the client mime type.
     *
     * @return null|string
     */
    public function mime()
    {
        return $this->getMimeType();
    }

    /**
     * save file
     * @param $dir
     * @param null $filename
     * @return \Symfony\Component\HttpFoundation\File\File
     */
    public function save($dir, $filename = null)
    {
        if(!$filename){
            $filename = $this->md5().'.'.$this->ext();
        }
        return $this->move($dir, $filename);
    }


    /**
     * get error code
     *
     * @return int
     */
    public function error()
    {
        return $this->getError();
    }

    /**
     * get error message
     *
     * @return string
     */
    public function message()
    {
        return $this->getErrorMessage();
    }

    /**
     * Create a new file instance from a base instance.
     *
     * @param  UploadedFile  $file
     * @param  bool $test
     * @return static
     */
    public static function createFromBase(UploadedFile $file, $test = false)
    {
        return $file instanceof static ? $file : new static(
            $file->getPathname(),
            $file->getClientOriginalName(),
            $file->getClientMimeType(),
            $file->getError(),
            $test
        );
    }
}
