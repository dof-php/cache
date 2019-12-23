<?php

declare(strict_types=1);

namespace DOF\Cache;

class Driver extends \DOF\Storage\Driver
{
    use \DOF\Storage\Traits\KVDriver;

    const LIST = [
        'file'  => \DOF\Cache\File::class,
        'redis' => \DOF\Cache\Redis::class,
        'memcached' => \DOF\Cache\Memcached::class,
    ];

    const KV = 'cache';
}
