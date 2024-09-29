<?php

namespace Xala\EloquentMock;

class QueryExpectation
{
    public string $sql;

    public array | null $bindings = [];

    public array $rows = [];

    public function __construct(string $sql)
    {
        $this->sql = $sql;
    }

    public function withBindings(array $bindings): static
    {
        $this->bindings = $bindings;

        return $this;
    }

    public function withAnyBindings(): static
    {
        $this->bindings = null;

        return $this;
    }

    public function andReturnRows(array $rows): static
    {
        $this->rows = $rows;

        return $this;
    }
}
