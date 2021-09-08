<?php

namespace engine\module;

use engine\bootable\IBootable;
use engine\business\db\DatabaseBoot;
use engine\route\RouteBoot;

abstract class ModuleBoot implements IBootable
{
    abstract protected static function haveDatabaseBoot(): bool;
    abstract protected static function getRouteBoot(): ?RouteBoot;

    public static function boot()
    {

        if (static::haveDatabaseBoot()) {
            DatabaseBoot::boot();
        }
        if (static::getRouteBoot() != null) {
            static::getRouteBoot()::boot();
        }
    }
}
