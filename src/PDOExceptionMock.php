<?php

namespace Xala\Elomock;

use PDOException;

class PDOExceptionMock extends PDOException
{
    public static function fromErrorInfo(string $message, string $code, string $driverMessage, int $driverCode): static
    {
        $exception = new self($message);

        $exception->code = $code;

        $exception->errorInfo = [$code, $driverCode, $driverMessage];

        return $exception;
    }

    // TODO: add constructor fromSQLState(string | int $code, string $driverErrorType, string $driverErrorMessage)
}
