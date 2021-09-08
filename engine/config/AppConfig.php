<?php

namespace engine\config;

use engine\config\Config;
use RuntimeException;

class AppConfig extends Config
{
    protected function validate(): void
    {
        if ($this->getValue('TEMPALTE_FOLDER') == null) {
            throw new RuntimeException("Missing Config Template Folder");
        }
    }

    public function getTemplateFolder(): string
    {
        return $this->getTypedValue(self::STRING, 'TEMPALTE_FOLDER');
    }
}
