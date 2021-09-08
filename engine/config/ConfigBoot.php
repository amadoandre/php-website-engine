<?php

namespace engine\config;

use engine\bootable\IBootable;
use RuntimeException;

abstract class ConfigBoot implements IBootable
{
    /**
    * @return IConfig::class[] array of classes and Keys for configuration
    * example array( DbConfig::class => 'DB' );
    */
    abstract protected static function getConfigClasses(): array;
    
    /**
    * @return string fullpath for configuration file
    */
    abstract protected static function getConfigFile(): string;


    final public static function boot()
    {
        $confs = self::load(static::getConfigFile());
        foreach (static::getConfigClasses() as $class => $confKey) {
            $class::create($confs[$confKey] ?? array());
        }
    }

    private static function load($file)
    {
        // # check file
        if (!is_file($file)) {
            throw new RuntimeException("No ConfigFile");
        }

        return  self::doParse($file, function ($f) {
            return json_decode(file_get_contents($f), true);
        });
    }
    private static function doParse(string $file, callable $parseFn)
    {
        if ($conf = $parseFn($file)) {
            if (!empty($conf)) {
                return $conf;
            } else {
                throw new RuntimeException("Loaded empty configuration from file '$file'.");
            }
        } else {
            throw new RuntimeException("Could not load configuration file: '$file'.");
        }
    }
}
