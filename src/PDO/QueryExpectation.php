<?php

namespace Xala\Elomock\PDO;

class QueryExpectation
{
    public string $query;

    public function __construct(string $query)
    {
        $this->query = $query;
    }
}
