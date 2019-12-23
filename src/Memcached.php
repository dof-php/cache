<?php

declare(strict_types=1);

namespace DOF\Cache;

use DOF\Cache\Cache;
use DOF\Cache\Exceptor\MemcachedExceptor;

class Memcached extends Cache
{
    public function has(string $key) : bool
    {
        $start = \microtime(true);

        $val = $this->connection()->get($this::name($key));

        $this->log('has', $start, $key);

        return !\is_null($val);
    }

    public function get(string $key)
    {
        $start = \microtime(true);

        $result = $this->connection()->get($this::name($key));

        $this->log('get', $start, $key);

        if ($this->connection()->getResultCode() === \Memcached::RES_NOTFOUND) {
            return null;
        }
        if (false === $result) {
            return null;
        }

        if (\is_string($result)) {
            $_result = \unserialize($result);
            if (($result === 'b:0;') || ($_result !== false)) {
                return $_result;
            }
        }

        return $result;
    }

    public function del(string $key)
    {
        $start = \microtime(true);

        $this->connection()->delete($this::name($key), 0);

        $this->log('del', $start, $key);
    }

    public function dels(array $keys)
    {
        $start = \microtime(true);

        $this->connection()->deleteMulti(\array_map(function ($key) {
            return $this::name($key);
        }, $keys), 0);

        $this->log('dels', $start, $keys, 0);
    }

    public function set(string $key, $value, int $expiration = 0)
    {
        $start = \microtime(true);

        if (\is_object($value)) {
            $value = \serialize($value);
        }

        $this->connection()->set($this::name($key), $value, $expiration);

        $this->log('set', $start, $key, $value, $expiration);
    }
}
