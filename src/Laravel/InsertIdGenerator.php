<?php

namespace Xala\Elomock\Laravel;

class InsertIdGenerator
{
    public int $lastInsertId = 1;

    public function generate(): int
    {
        return $this->lastInsertId++;
    }
}
