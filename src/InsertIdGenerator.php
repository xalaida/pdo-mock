<?php

namespace Xala\Elomock;

class InsertIdGenerator
{
    public int $lastInsertId = 1;

    public function generate(): int
    {
        return $this->lastInsertId++;
    }
}
