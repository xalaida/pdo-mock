<?php

namespace Xala\EloquentMock;

class FakeLastInsertIdGenerator
{
    public int $lastInsertId = 1;

    public function generate(): string
    {
        return (string) $this->lastInsertId++;
    }
}
