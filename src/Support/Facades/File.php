<?php

use Illuminate\Support\Facades\Facade;

/**
 * Class File
 *
 * @method static \VM\FileSystem\FileSystem bool has(string $path)
 * @method static \VM\FileSystem\FileSystem mixed get(string $path, bool $lock = false)
 * @method static \VM\FileSystem\FileSystem bool del(string $path)
 * @method static \VM\FileSystem\FileSystem set(string $path, string $contents, bool $lock = false)
 * @method static \VM\FileSystem\FileSystem put(string $path, string $contents, bool $lock = false)
 * @method static \VM\FileSystem\FileSystem copy(string $path, string $target)
 * @method static \VM\FileSystem\FileSystem move(string $path, string $target)
 * @method static \VM\FileSystem\FileSystem size(string $path)
 * @method static \VM\FileSystem\FileSystem type(string $path)
 * @method static \VM\FileSystem\FileSystem mimeType(string $path)
 * @method static \VM\FileSystem\FileSystem name(string $path)
 * @method static \VM\FileSystem\FileSystem dirname(string $path)
 * @method static \VM\FileSystem\FileSystem basename(string $path)
 * @method static \VM\FileSystem\FileSystem extension(string $path)
 * @method static \VM\FileSystem\FileSystem glob(string $pattern, int $flags = 0)
 * @method static \VM\FileSystem\FileSystem files(string $directory)
 * @method static \VM\FileSystem\FileSystem allFiles(string $directory, bool $hidden = false)
 * @method static \VM\FileSystem\FileSystem isFile(string $file)
 * @method static \VM\FileSystem\FileSystem isDir(string $path)
 * @method static \VM\FileSystem\FileSystem mkDir(string $path, int $mode = 0755, bool $recursive = true, bool $force = false)
 * @method static \VM\FileSystem\FileSystem copyDir(string $dir, string $target, mixed $options = null)
 * @method static \VM\FileSystem\FileSystem exists(string $path)
 * @method static \VM\FileSystem\FileSystem append(string $path, string $data)
 * @method static \VM\FileSystem\FileSystem prepend(string $path, string $data)
 * @method static \VM\FileSystem\FileSystem directories(string $directory)
 * @method static \VM\FileSystem\FileSystem isDirectory(string $directory)
 * @method static \VM\FileSystem\FileSystem makeDirectory(string $path, int $mode = 0755, bool $recursive = false, bool $force = false)
 * @method static \VM\FileSystem\FileSystem moveDirectory(string $from, string $to, bool $overwrite = false)
 * @method static \VM\FileSystem\FileSystem copyDirectory(string $directory, string $destination, int $options = null)
 * @method static \VM\FileSystem\FileSystem deleteDirectory(string $directory, bool $preserve = false)
 * @method static \VM\FileSystem\FileSystem cleanDirectory(string $directory, bool $preserve = false)
 * @method static \VM\FileSystem\FileSystem sharedGet(string $path)
 * @method static \VM\FileSystem\FileSystem getRequire(string $path)
 * @method static \VM\FileSystem\FileSystem requireOnce(string $path)
 * @method static \VM\FileSystem\FileSystem lastModified(string $path)
 * @method static \VM\FileSystem\FileSystem isWritable(string $path)
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
