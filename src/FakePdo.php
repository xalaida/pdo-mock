<?php

namespace Xala\EloquentMock;

use RuntimeException;

class FakePdo
{
    public string | false $lastInsertId = false;

    public function __call(string $name, array $arguments)
    {
        throw new RuntimeException("Unexpected PDO method call: {$name}");
    }

    public function lastInsertId(): string | false
    {
        $lastInsertId = $this->lastInsertId;

        $this->lastInsertId = false;

        return $lastInsertId;
    }
}
