<?php

declare(strict_types=1);

namespace DOF\Cache;

interface Cachable
{
    public function has(string $key) : bool;

    public function get(string $key);

    public function set(string $key, $value, int $expiration = 0);

    public function del(string $key);

    public function dels(array $keys);
}
