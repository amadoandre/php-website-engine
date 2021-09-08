<?php

namespace engine\config;

use RuntimeException;

/**
 * singleton Config
 */
abstract class Config implements IConfig
{

    private static $instance;
    private array $configs;

    protected const STRING ='STRING';
    protected const BOOL ='BOOL';
    protected const INT ='INT';

    protected function __construct(array $configs)
    {
        $this->configs = $configs;
        $this->validate();
    }

    public final static function create(array $configs): IConfig
    {
        if (self::$instance != null) {
            throw new RuntimeException("Config already created: " . static::class);
        }
        self::$instance = new static($configs);
        return self::$instance;
    }

    public final  static function getInstance(): static
    {
        if (self::$instance == null) {
            throw new RuntimeException("No config created: " . static::class);
        }
        return self::$instance;
    }

    protected function getValue(string $name): ?string
    {
        return $this->configs[$name] ?? null;
    }
    protected function getTypedValue($type, string $name): mixed
    {
        $key = $this->configs[$name] ?? null;

        $filtered = null;
        switch ($type) {
            case self::STRING:
                $filtered = is_string($key) ? filter_var($key, FILTER_SANITIZE_STRING) : null;
                break;
            case self::BOOL:
                $filtered = is_string($key) ? filter_var($key, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;
                break;
            case self::INT:
                $filtered = is_int($key) ? filter_var($key, FILTER_VALIDATE_INT) : null;
                break;
            default:
                throw new RuntimeException("Not implemented type : $type");

                break;
        }
        return  $filtered;
    }


    protected function getConfigs()
    {
        return $this->configs;
    }
    protected abstract function validate(): void;
}
