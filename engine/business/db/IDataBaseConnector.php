<?php

namespace engine\business\db;

interface IDataBaseConnector
{
    /**
     * Create the TransactionBlock needed
     * @param callable $funcBlock - function(IQueryExecuter $executor)
     * @return ExecuterResponseWrapper - retrun with return of funcBlock
     */
    public function executeTransactionalBlock(callable $funcBlock): ExecuterResponseWrapper;
    /**
     * Create an non transactional block , with no possible rolback
     * @param callable $funcBlock - function(IQueryExecuter $executor)
     * @return ExecuterResponseWrapper - retrun with return of funcBlock
     */
    public function executeNonTransactionalBlock(callable $funcBlock): ExecuterResponseWrapper;
}
