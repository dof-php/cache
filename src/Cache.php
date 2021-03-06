<?php

declare(strict_types=1);

namespace DOF\Cache;

use Throwable;
use DOF\Util\Str;
use DOF\Util\Format;
use DOF\Storage\Connection;
use DOF\Cache\Driver;
use DOF\Cache\Cachable;
use DOF\Cache\Exceptor\CacheExceptor;

abstract class Cache implements Cachable
{
    use \DOF\Storage\Traits\LogableStorage;

    const STORAGE = 'cache';
    const PREFIX = '__CACHE';

    public function flush(string $key)
    {
        if ($this->has($key)) {
            $value = $this->get($key);
            $this->del($key);
            return $value;
        }
    }

    final public static function name(string $key) : string
    {
        return \join(':', [self::PREFIX, $key]);
    }
}
