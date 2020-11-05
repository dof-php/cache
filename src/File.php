<?php

declare(strict_types=1);

namespace DOF\Cache;

use DOF\Util\FS;
use DOF\Util\Str;
use DOF\Util\Arr;
use DOF\Cache\Cache;
use DOF\Cache\Exceptor\FileExceptor;

/**
 * Data structure of file cache must be an index array
 *
 * #1: Expiration timestamp
 * #2: Base64 encoded cache data
 */
class File extends Cache
{
    private function parse(string $path)
    {
        $cache = include $path;
        if (! \is_array($cache)) {
            throw new FileExceptor('INVALID_FILE_CACHE', \compact('cache'));
        }
        $expire = $cache[0] ?? null;
        if (! \is_int($expire)) {
            throw new FileExceptor('INVALID_FILE_CACHE_EXPIRATION', \compact('expire'));
        }

        return [$expire, ($cache[1] ?? null)];
    }

    private function path(string $key) : string
    {
        $path = $this->options['path'] ?? null;
        if ((! $path) || (false === FS::mkdir($path))) {
            throw new FileExceptor('UNWRITABLE_FILE_CACHE_PATH', \compact('path'));
        }

        $hash = \md5($key);
        $dir = Str::first($hash, 2);

        return FS::path($path, $dir, Str::last($hash, 30));
    }
   
    public function has(string $key) : bool
    {
        $start = \microtime(true);

        $has = false;

        \clearstatcache();
        $path = $this->path($key);
        if (\is_file($path)) {
            list($expire, $value) = $this->parse($path);
            if (($expire > 0) && ($start >= $expire)) {
                \unlink($path);
            } else {
                $has = true;
            }
        }

        $this->log('has', $start, $key);

        return $has;
    }

    public function get(string $key)
    {
        $start = \microtime(true);

        \clearstatcache();

        $value = null;
        $path = $this->path($key);
        if (\is_file($path)) {
            list($expire, $value) = $this->parse($path);
            if (($expire > 0) && ($start >= $expire)) {
                \unlink($path);
            } else {
                $_value = \base64_decode($value);
                if ($_value === 'b:0;') {
                    $value = false;
                }
                $_value = \unserialize($_value);
                if (false !== $_value) {
                    $value = $_value;
                }
            }
        }

        $this->log('get', $start, $key);

        return $value;
    }

    public function del(string $key)
    {
        $start = \microtime(true);

        if (\is_file($path = $this->path($key))) {
            \unlink($path);
        }

        $this->log('del', $start, $key);
    }

    public function dels(array $keys)
    {
        $start = \microtime(true);

        foreach ($keys as $key) {
            if (\is_file($path = $this->path($key))) {
                \unlink($path);
            }
        }

        $this->log('dels', $start, $keys, 0);
    }

    public function set(string $key, $value, int $expiration = 0)
    {
        $start = \microtime(true);

        $value = \base64_encode(\serialize($value));
        if ($expiration > 0) {
            $expiration = \intval($start) + $expiration;
        }

        Arr::save([$expiration, $value], $this->path($key));

        $this->log('set', $start, $key, $value, $expiration);
    }
}
