<?php

namespace Xalaida\PDOMock;

use PDOException;

class PDOMockException extends PDOException
{
    /**
     * @param string $message
     * @param string $code
     * @param string $driverMessage
     * @param int $driverCode
     * @return self
     */
    public static function fromErrorInfo($message, $code, $driverMessage, $driverCode)
    {
        $exception = new self($message);

        $exception->code = $code;

        $exception->errorInfo = [$code, $driverCode, $driverMessage];

        return $exception;
    }
}
