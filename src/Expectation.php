<?php

namespace Xala\Elomock;

use Closure;
use InvalidArgumentException;
use PDO;
use PDOException;

class Expectation
{
    public string $query;

    public bool | null $prepared = null;

    public array | Closure | null $bindings = null;

    public int $rowCount = 0;

    public array $rows = [];

    public ?string $insertId = null;

    public ?PDOException $exceptionOnExecute = null;

    public ?PDOException $exceptionOnPrepare = null;

    public ?PDOStatementMock $statement = null;

    public function __construct(string $query)
    {
        $this->query = $query;
    }

    public function toBePrepared(bool $prepared = true): static
    {
        $this->prepared = $prepared;

        return $this;
    }

    public function withBinding(string | int $key, mixed $value, int $type = PDO::PARAM_STR): static
    {
        $this->bindings[$key] = [
            'value' => $value,
            'type' => $type,
        ];

        return $this;
    }

    public function withBindings(array $bindings, bool $shouldUseValueType = false): static
    {
        foreach ($bindings as $key => $value) {
            $param = is_int($key)
                ? $key + 1
                : $key;

            $type = $shouldUseValueType
                ? $this->getTypeFromValue($value)
                : PDO::PARAM_STR;

            $this->bindings[$param] = [
                'value' => $value,
                'type' => $type,
            ];
        }

        return $this;
    }

    public function withBindingsUsing(Closure $callback): static
    {
        $this->bindings = $callback;

        return $this;
    }

    public function withInsertId(string $insertId): static
    {
        $this->insertId = $insertId;

        return $this;
    }

    public function affecting(int $rowCount): static
    {
        $this->rowCount = $rowCount;

        return $this;
    }

    public function andFetchRows(array $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    public function andFetchRow(array $row): static
    {
        $this->rows = [$row];

        return $this;
    }

    public function andFail(PDOException $exception): static
    {
        $this->exceptionOnExecute = $exception;

        return $this;
    }

    public function andFailOnPrepare(PDOException $exception): static
    {
        $this->exceptionOnPrepare = $exception;

        return $this;
    }

    protected function getTypeFromValue(mixed $value): int
    {
        if (is_string($value)) {
            return PDO::PARAM_STR;
        }

        if (is_int($value)) {
            return PDO::PARAM_INT;
        }

        if (is_bool($value)) {
            return PDO::PARAM_BOOL;
        }

        throw new InvalidArgumentException('Unsupported type: ' . gettype($value));
    }
}
