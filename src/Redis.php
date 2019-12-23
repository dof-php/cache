<?php

declare(strict_types=1);

namespace DOF\Cache;

use Closure;
use DOF\Util\Format;
use DOF\Util\TypeHint;
use DOF\Cache\Exceptor\RedisExceptor;

class Redis extends Cache
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

        if ($result === false) {
            return null;
        }

        $_result = \unserialize($result);

        // https://stackoverflow.com/questions/1369936/check-to-see-if-a-string-is-serialized
        if (($result === 'b:0;') || ($_result !== false)) {
            return $_result;
        }

        return $result;
    }

    /**
     * Cachable setter not the redis string set() method
     */
    public function set(string $key, $value, int $expiration = 0)
    {
        $_value = \serialize($value);

        $start = \microtime(true);
        if ($expiration > 0) {
            $this->connection()->setEx($this::name($key), $expiration, $_value);
            $this->log('set', $start, $key, $expiration, $_value);
        } else {
            $this->connection()->set($this::name($key), $_value);
            $this->log('set', $start, $key, $_value);
        }
    }

    public function dels(array $keys)
    {
        $start = \microtime(true);

        $this->connection()->del(\array_map(function ($key) {
            return $this::name($key);
        }, $keys));

        $this->log('dels', $start, $keys);
    }

    public function del(string $key)
    {
        $start = \microtime(true);

        $this->connection()->del($this::name($key));

        $this->log('del', $start, $key);
    }
}
