<?php

namespace engine\business\db;

interface IQueryExecuter
{

    public function executeQuery(string $sql, ?array $args = array()): ?array;

}
