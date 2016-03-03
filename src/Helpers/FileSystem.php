<?php namespace Crip\Core\Helpers;

use ErrorException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

/**
 * Class FileSystem
 * @package Crip\Core\Helpers
 */
class FileSystem
{
    /**
     * Splits the file system path.
     *
     * @param string $path The path to split.
     *
     * @return array The split path.
     */
    public static function split($path)
    {
        return preg_split('/[\\\\\/]+/', $path);
    }

    /**
     * Joins a split file system path.
     *
     * @param array $path The split path.
     *
     * @return string The joined path.
     */
    public static function join(array $path)
    {
        return join(DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Canonicalizes the file system path. Always remove slash from the end.
     *
     * @param string $path The path to canoncalize.
     *
     * @return string The canoncalized path.
     */
    public static function canonical($path)
    {
        $path = self::split($path);
        $canon = [];
        foreach ($path as $segment) {
            if ($segment === '..') {
                array_pop($canon);
            } elseif ($segment !== '.') {
                $canon[] = $segment;
            }
        }

        if ($canon[count($canon) - 1] === '') {
            array_pop($canon);
        }

        return self::join($canon);
    }

    /**
     * Determine if a file or directory exists.
     *
     * @param  string $path
     * @return bool
     */
    public static function exists($path)
    {
        return file_exists($path);
    }

    /**
     * Create a directory.
     *
     * @param  string $path
     * @param  int $mode
     * @param  bool $recursive
     * @param  bool $force
     * @return bool
     */
    public static function mkdir($path, $mode = 0755, $recursive = false, $force = false)
    {
        if (!self::exists($path)) {
            if ($force) {
                return @mkdir($path, $mode, $recursive);
            }

            return mkdir($path, $mode, $recursive);
        }

        return true;
    }

    /**
     * Get the file type of a given file.
     *
     * @param  string $path
     * @return string
     */
    public static function type($path)
    {
        return filetype($path);
    }

    /**
     * Get the file name from provided path
     *
     * @param string $path
     *
     * @return string
     */
    public static function nameFromPath($path)
    {
        return basename($path);
    }

    /**
     * Trim file name from provided path
     *
     * @param string $path
     *
     * @return string
     */
    public static function trimFileName($path)
    {
        $name = static::nameFromPath($path);

        return substr($path, 0, strlen($path) - strlen($name));
    }

    /**
     * Get the path separate from name
     *
     * @param string $path
     *
     * @return array
     */
    public static function splitNameFromPath($path)
    {
        $name = static::nameFromPath($path);
        $path = static::trimFileName($path);

        return [$path, $name];
    }

    /**
     * Get the file mime content type from provided path
     *
     * @param string $path
     * @return string
     *
     * @throws FileNotFoundException
     */
    public static function getMimeType($path)
    {
        if (static::exists($path)) {
            return mime_content_type($path);
        }

        throw new FileNotFoundException();
    }

    /**
     * Delete the file at a given path.
     *
     * @param  string|array $paths
     * @return bool
     */
    public static function delete($paths)
    {
        $paths = is_array($paths) ? $paths : func_get_args();
        $deleted = true;

        foreach ($paths as $path) {
            try {
                if (!@unlink($path)) {
                    $deleted = false;
                }
            } catch (ErrorException $e) {
                $deleted = false;
            }
        }

        return $deleted;
    }
}