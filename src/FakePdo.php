<?php

namespace Xala\EloquentMock;

use RuntimeException;

class FakePdo
{
    public function __call(string $name, array $arguments)
    {
        throw new RuntimeException("Unexpected PDO method call: {$name}");
    }
}
