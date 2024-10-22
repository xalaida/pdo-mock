<?php

namespace Xala\Elomock\Laravel;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Expectation
{
    public string $query;

    public array | Closure | null $bindings = [];

    public array | Collection | Closure $rows = [];

    public int $rowCount = 1;

    public ?FailedQueryException $exception = null;

    public string | false $lastInsertId = false;

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

    public function andReturnRows(array | Collection | Closure $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    public function andReturnRowsUsing(Closure $callback): static
    {
        $this->andReturnRows($callback);

        return $this;
    }

    public function andReturnRow(array | Model $row): static
    {
        return $this->andReturnRows([$row]);
    }

    public function andReturnNothing(): static
    {
        $this->rows = [];

        return $this;
    }

    public function andReturnCount(int $count): static
    {
        $this->rowCount = $count;

        return $this;
    }

    public function andFail(string $message = 'Query error'): static
    {
        $this->exception = new FailedQueryException($message);

        return $this;
    }
}
