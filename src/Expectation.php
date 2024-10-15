<?php

namespace Xala\Elomock;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Model;

class Expectation
{
    public string $query;

    public array | Closure | null $bindings = [];

    public string | false $lastInsertId = false;

    public array $rows = [];

    public bool $successfulStatement = true;

    public int $affectedRows = 1;

    public ?Exception $exception = null;

    public function __construct(string $query, array | Closure | null $bindings = null)
    {
        $this->query = $query;
        $this->bindings = $bindings;
    }

    public function withBindings(array | Closure $bindings): static
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

    public function andReturnRow(array|Model $row): static
    {
        if ($row instanceof Model) {
            $row = $row->getAttributes();
        }

        $this->rows = [$row];

        return $this;
    }

    public function andReturnNothing(): static
    {
        $this->rows = [];

        return $this;
    }

    public function andThrow(string $message = ''): static
    {
        $this->exception = new FakePdoException($message);

        return $this;
    }

    public function asSuccessfulStatement(): static
    {
        $this->successfulStatement = true;

        return $this;
    }

    // TODO: consider removing this method and use andThrow() instead
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
