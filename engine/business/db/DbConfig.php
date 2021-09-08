<?php

namespace engine\business\db;

use engine\config\Config;
use RuntimeException;

class DbConfig extends Config
{


    protected function validate(): void
    {
        if ($this->getValue('HOSTNAME') == null) {
            throw new RuntimeException("Missing Config HOSTNAME");
        }
        if ($this->getValue('DB_NAME') == null) {
            throw new RuntimeException("Missing Config DB_NAME");
        }
        if ($this->getValue('USERNAME') == null) {
            throw new RuntimeException("Missing Config USERNAME");
        }
        if ($this->getValue('PASSWORD') == null) {
            throw new RuntimeException("Missing Config PASSWORD");
        }
    }

    public function getHostName(): string
    {
        return $this->getTypedValue(self::STRING, 'HOSTNAME');
    }
    public function getDbName(): string
    {
        return $this->getTypedValue(self::STRING, 'DB_NAME');
    }
    public function getUserName(): string
    {
        return $this->getTypedValue(self::STRING, 'USERNAME');
    }
    public function getPassword(): string
    {
        return $this->getTypedValue(self::STRING, 'PASSWORD');
    }
}
