<?php

namespace Xala\EloquentMock;

class QueryExpectation
{
    public string $sql;

    public array | null $bindings = [];

    public string | false $lastInsertId = false;

    public array $rows = [];

    public bool $successfulStatement = true;

    public int $affectedRows = 1;

    public ?FakePdoException $exception = null;

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

    public function withLastInsertId(string $id): static
    {
        $this->lastInsertId = $id;

        return $this;
    }

    public function andReturnRows(array $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    // TODO: consider removing asFailedStatement() method
    public function andThrow(string $message): static
    {
        $this->exception = new FakePdoException($message);

        return $this;
    }

    public function asSuccessfulStatement(): static
    {
        $this->successfulStatement = true;

        return $this;
    }

    public function asFailedStatement(): static
    {
        $this->successfulStatement = false;

        return $this;
    }

    public function andAffectRows(int $rows): static
    {
        $this->affectedRows = $rows;

        return $this;
    }
}
