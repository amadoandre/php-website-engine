<?php

namespace engine\business;

use engine\business\db\DataBaseConnector;
use engine\business\db\IDataBaseConnector;

abstract class BusinessService
{
    private static $instance;
    protected IDataBaseConnector $dbCon;

    protected function __construct(IDataBaseConnector $dbCon)
    {
        $this->dbCon = $dbCon;
    }

    final public static function getInstance(): static
    {
        if (self::$instance == null) {
            self::$instance = new static(DataBaseConnector::getInstance());
        }
        return self::$instance;
    }



}
