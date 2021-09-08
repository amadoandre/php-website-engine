<?php

namespace engine\business\db;

use engine\bootable\IBootable;

class DatabaseBoot implements IBootable
{
    public static function boot()
    {
        DataBaseConnector::create(DbConfig::getInstance());
    }
}
