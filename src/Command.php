<?php

declare(strict_types=1);

namespace DOF\Cache;

use Throwable;
use DOF\DOF;
use DOF\Convention;
use DOF\Util\IS;
use DOF\Util\FS;
use DOF\Util\Arr;

class Command
{
    /**
     * @CMD(cache.file.clear)
     * @Desc(Clear all file cache in DOF project)
     */
    public function clearFileCache($console)
    {
        $cache = DOF::path(Convention::DIR_RUNTIME, Convention::DIR_CACHE);
        $_cache = DOF::pathof($cache);
        $console->task("Remove project file cache ({$_cache}) ...", function () use ($cache) {
            FS::unlink($cache);
        });
    }

    /**
     * @CMD(cache.file.check)
     * @Desc(Check and clear all timeouted cache files)
     */
    public function checkFileCache($console)
    {
        FS::walkr(DOF::path(Convention::DIR_RUNTIME, Convention::DIR_CACHE), function ($file) use ($console) {
            if (\is_array($res = Arr::load($path = $file->getRealpath(), false)) && IS::timestamp($ts = ($res[0] ?? null)) && (\time() >= $ts)) {
                $_path = DOF::pathof($path);
                $console->task("Clearing timeouted file cache: {$_path}", function () use ($path) {
                    FS::unlink($path);
                });
            }
        });
    }
}
