<?php

namespace engine\business\db;

use engine\business\db\DbConfig;
use engine\business\db\DataBaseErrorType;
use engine\business\db\NeedTransactionRollbackException;
use engine\business\db\ExecuterResponseWrapper;
use engine\business\db\IDataBaseConnector;
use engine\business\db\IQueryExecuter;
use Exception;
use RuntimeException;

final class DataBaseConnector implements IQueryExecuter, IDataBaseConnector
{
    private static $instance;

    private $server;
    private $user;
    private $password;
    private $db;

    private $connection;
    private $transactionOn;

    private function __construct(DbConfig $config)
    {

        $this->server = $config->getHostName();
        $this->user = $config->getUserName();
        $this->password = $config->getPassword();
        $this->db = $config->getDbName();
        $this->connection = null;
        $this->transactionOn = false;
    }

    public function __destruct()
    {
        if ($this->transactionOn && $this->connection != null) {
            $this->rollbackTransaction();
        }
            $this->closeConnection();
    }

    public final static function create(DbConfig $config): IDataBaseConnector
    {
        if (self::$instance != null) {
            throw new RuntimeException("Config already created: " . static::class);
        }
        self::$instance = new DataBaseConnector($config);
        return self::$instance;
    }

    public final  static function getInstance(): IDataBaseConnector
    {
        if (self::$instance == null) {
            throw new RuntimeException("No config created: " . static::class);
        }
        return self::$instance;
    }


    /************
     *  DB API  *
     ************/
    public function executeTransactionalBlock(callable $funcBlock): ExecuterResponseWrapper
    {
        $ret = null;
        try {
            if (!$this->startTransaction()) {
                $ret = ExecuterResponseWrapper::withError(DataBaseErrorType::NO_CONNECTION, "fail to start transaction");
            } else {
                try {
                    $retBlock = $funcBlock($this);
                } catch (NeedTransactionRollbackException $e) {
                    $this->rollbackTransaction();
                    return ExecuterResponseWrapper::withError(DataBaseErrorType::EXECUTION_ERROR, $e->getMessage());
                } catch (RunTimeException $e) {
                    $this->rollbackTransaction();
                    return ExecuterResponseWrapper::withError(DataBaseErrorType::EXECUTION_ERROR, $e->getMessage());
                }
                if (!$this->commitTransaction()) {
                    if (!$this->rollbackTransaction()) {
                        error_log("transactional : can't rollback transaction");
                    }
                    $ret = ExecuterResponseWrapper::withError(DataBaseErrorType::COMMIT_ERROR, "fail to commit transaction");
                } else {
                    $ret = ExecuterResponseWrapper::withSuccess($retBlock);
                }
            }
        } catch (\Throwable $th) {
            error_log("transactional : " . $th->getMessage());
            $ret = ExecuterResponseWrapper::withError(DataBaseErrorType::EXECUTION_ERROR, $th->getMessage());
        }
        return $ret;
    }
    public function executeNonTransactionalBlock(callable $funcBlock): ExecuterResponseWrapper
    {
        $ret = null;
        try {
            if (!$this->init()) {
                $ret = ExecuterResponseWrapper::withError(DataBaseErrorType::NO_CONNECTION, "fail to start connection");
            } else {
                $retBlock = $funcBlock($this);
                $ret = ExecuterResponseWrapper::withSuccess($retBlock);
            }
        } catch (\Throwable $th) {
            error_log("non transactional : " . $th->getMessage());
            $ret = ExecuterResponseWrapper::withError(DataBaseErrorType::EXECUTION_ERROR(), $th->getMessage());
        }
        return $ret;
    }
    /*************
     *Queries API*
     *************/

    public function executeQuery(string $sql, ?array $args = array()): ?array
    {

        if ($this->connection == null) {
           throw new RunTimeException('Executing query: No connection.');
        }
        $sql_type = $this->getSqlType($sql);
        if ($sql == null) {
            throw new RunTimeException('Executing query: Invalid query Type:' . $sql);
        }

        $res = null;
        $stmt = pg_prepare($this->connection, "query", $sql);
        if (!$stmt) {
            throw new RunTimeException('Executing query: Wrong query :' . $sql);
        } else {

            $result = pg_execute($this->connection, "query", $args);
            if (!$result) {
                throw new RunTimeException('Executing query: Wrong paramenters: ' . json_encode($args));
            } else {
                if (in_array($sql_type, array("UPDATE", "DELETE"))) {
                    echo 1;
                    $res = array('affected_rows' => pg_affected_rows($result));
                } elseif ($sql_type == "INSERT") {
                    echo 2;
                    $res = array('id' => pg_last_oid($result));
                } elseif ($sql_type == "SELECT") {
                    $res = pg_fetch_all($result, PGSQL_ASSOC);
                } else {
                    $res = null;
                }
            }
        }
        return  $res;
    }

    /**********
     * HELPERS *
     ***********/
    private function init(): bool
    {
        if ($this->connection == null) {
            $this->connection = pg_connect("host=$this->server dbname=$this->db user=$this->user password=$this->password");
            if (!$this->connection) {
                error_log('init: Cant connect to server ' . $this->server);
                return false;
            }
        }
        return true;
    }
    private function closeConnection(): void
    {
        if ($this->connection != null) {
            pg_close($this->connection);
        }
    }
    private function startTransaction(): bool
    {
        $ret = false;
        if ($this->connection == null && !$this->init()) {
            error_log('StartTransaction: No connection.');
            $ret = false;
        } else {
            if ($this->transactionOn) {
                error_log('StartTransaction: Transaction already in use');
                $ret = false;
            } else {
                if ($this->connection != null) {
                    $this->transactionOn  = pg_query($this->connection, 'BEGIN') ? true : false;
                    if (!$this->transactionOn) {
                        error_log('StartTransaction: Cant strat transaction');
                    }
                    $ret = $this->transactionOn;
                } else {
                    error_log('StartTransaction: No connection.');
                    $ret = false;
                }
            }
        }
        return $ret;
    }
    private function commitTransaction(): bool
    {
        if ($this->connection == null) {
            error_log('CommitTransaction: No connection.');
            return false;
        }
        if (!$this->transactionOn) {
            error_log('CommitTransaction: No transaction.');
            return false;
        }
        $ret =  pg_query($this->connection, "COMMIT;") ? true : false;
        if ($ret) {
            $this->transactionOn = false;
        }
        return $ret;
    }
    private function rollbackTransaction(): bool
    {
        if ($this->connection == null) {
            error_log('RollbackTransaction: No connection.');
            return false;
        }
        if (!$this->transactionOn) {
            error_log('RollbackTransaction: No transaction.');
            return false;
        }
        $ret = pg_query($this->connection, 'ROLLBACK') ? true : false;
        if (!$ret) {
            $this->transactionOn = false;
        }

        return $ret;
    }
    private function getSqlType($sql): ?string
    {
        preg_match("/^(\w+)/", strtoupper(trim($sql)), $sql_type);
        if (isset($sql_type[1]) && in_array($sql_type[1], array("UPDATE", "DELETE", "INSERT", "SELECT"))) {
            return $sql_type[1];
        }
        return null;
    }
}
