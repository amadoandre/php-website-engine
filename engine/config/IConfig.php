<?php

namespace engine\config;

use engine\bootable\IBootable;

interface IConfig
{

    public static function getInstance(): static;
    public static function create(array $configs): IConfig;
}
