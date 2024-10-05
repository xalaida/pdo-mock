<?php

namespace Xala\EloquentMock;

use RuntimeException;

/**
 * @mixin \PDO
 */
class FakePdo
{
    public string | false $lastInsertId = false;

    public function __construct(
        public FakeLastInsertIdGenerator | null $lastInsertIdGenerator
    ) {
    }

    public function __call(string $name, array $arguments)
    {
        throw new RuntimeException("Unexpected PDO method call: {$name}");
    }

    public function lastInsertId(): string | false
    {
        if ($this->lastInsertId !== false) {
            $lastInsertId = $this->lastInsertId;

            $this->lastInsertId = false;

            return $lastInsertId;
        }

        if ($this->lastInsertIdGenerator) {
            return $this->lastInsertIdGenerator->generate();
        }

        return false;
    }
}
