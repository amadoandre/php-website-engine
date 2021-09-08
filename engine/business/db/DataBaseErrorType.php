<?php

namespace engine\business\db;


enum DataBaseErrorType: string
{
    case NO_CONNECTION = 'NO_CONNECTION';
    case EXECUTION_ERROR = 'EXECUTION_ERROR';
    case COMMIT_ERROR = 'COMMIT_ERROR';
}
