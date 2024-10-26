<?php

namespace Xala\Elomock;

use InvalidArgumentException;
use PDO;
use PDOException;

class QueryExpectation
{
    public string $query;

    public bool | null $prepared = null;

    public array | null $bindings = null;

    public int $rowCount = 0;

    public array $rows = [];

    public ?string $insertId = null;

    public ?PDOException $exception = null;

    public function __construct(string $query)
    {
        $this->query = $query;
    }

    public function toBePrepared(bool $prepared = true): static
    {
        $this->prepared = $prepared;

        return $this;
    }

    public function withBinding(string $key, mixed $value, int $type = PDO::PARAM_STR): static
    {
        $this->bindings[$key] = [
            'value' => $value,
            'type' => $type,
        ];

        return $this;
    }

    public function withBindings(array $bindings, bool $shouldInheritTypes = false): static
    {
        foreach ($bindings as $key => $value) {
            $param = is_int($key)
                ? $key + 1
                : $key;

            $type = $shouldInheritTypes
                ? $this->inheritTypeFromValue($value)
                : PDO::PARAM_STR;

            $this->bindings[$param] = [
                'value' => $value,
                'type' => $type,
            ];
        }

        return $this;
    }

    public function withInsertId(string $insertId): static
    {
        $this->insertId = $insertId;

        return $this;
    }

    public function affectRows(int $rowCount): static
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

    public function andFail(string $errorMessage): static
    {
        $this->exception = new PDOException($errorMessage);

        return $this;
    }

    protected function inheritTypeFromValue(mixed $value): int
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
