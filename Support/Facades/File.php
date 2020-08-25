<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class File
 *
 * @method static bool has(string $path)
 * @method static mixed get(string $path, bool $lock = false)
 * @method static bool del(string $path)
 * @method static set(string $path, string $contents, bool $lock = false)
 * @method static put(string $path, string $contents, bool $lock = false)
 * @method static copy(string $path, string $target)
 * @method static move(string $path, string $target)
 * @method static size(string $path)
 * @method static type(string $path)
 * @method static mimeType(string $path)
 * @method static name(string $path)
 * @method static dirname(string $path)
 * @method static basename(string $path)
 * @method static extension(string $path)
 * @method static glob(string $pattern, int $flags = 0)
 * @method static files(string $directory)
 * @method static allFiles(string $directory, bool $hidden = false)
 * @method static isFile(string $file)
 * @method static isDir(string $path)
 * @method static mkDir(string $path, int $mode = 0755, bool $recursive = true, bool $force = false)
 * @method static copyDir(string $dir, string $target, mixed $options = null)
 * @method static exists(string $path)
 * @method static append(string $path, string $data)
 * @method static prepend(string $path, string $data)
 * @method static directories(string $directory)
 * @method static isDirectory(string $directory)
 * @method static makeDirectory(string $path, int $mode = 0755, bool $recursive = false, bool $force = false)
 * @method static moveDirectory(string $from, string $to, bool $overwrite = false)
 * @method static copyDirectory(string $directory, string $destination, int $options = null)
 * @method static deleteDirectory(string $directory, bool $preserve = false)
 * @method static cleanDirectory(string $directory, bool $preserve = false)
 * @method static sharedGet(string $path)
 * @method static getRequire(string $path)
 * @method static requireOnce(string $path)
 * @method static lastModified(string $path)
 * @method static isWritable(string $path)
 */
class File extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'file';
    }
}
