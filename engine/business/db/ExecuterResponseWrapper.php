<?php

namespace engine\business\db;

class ExecuterResponseWrapper
{
    protected $success;
    protected $result;
    protected $erroType;
    protected $errorMessage;

    public static function withSuccess($result): ExecuterResponseWrapper
    {
        return new ExecuterResponseWrapper(true, $result, null, null);
    }
    public static function withError(DataBaseErrorType $erroType, $errorMessage): ExecuterResponseWrapper
    {
        return new ExecuterResponseWrapper(false, null, $erroType, $errorMessage);
    }

    private function __construct(bool $success, $result, $erroType, $errorMessage)
    {
        $this->success = $success;
        $this->result = $result;
        $this->erroType = $erroType;
        $this->errorMessage = $errorMessage;
    }


    public function isSuccess(): bool
    {
        return $this->success;
    }
    public function getResult()
    {
        return $this->result;
    }
    public function getErroType(): DataBaseErrorType
    {
        return $this->erroType;
    }
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
