<?php

namespace Xala\Elomock\PDO;

use InvalidArgumentException;
use PDO;

class QueryExpectation
{
    public string $query;

    public bool $prepared = false;

    public array $bindings = [];

    public array $rows = [];

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

    public function withBindings(array $bindings, bool $shouldResolveTypes = false): static
    {
        foreach ($bindings as $key => $value) {
            $param = is_int($key)
                ? $key + 1
                : $key;

            $type = $shouldResolveTypes
                ? $this->resolveTypeFromValue($value)
                : PDO::PARAM_STR;

            $this->bindings[$param] = [
                'value' => $value,
                'type' => $type,
            ];
        }

        return $this;
    }

    public function andReturnRows(array $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    protected function resolveTypeFromValue(mixed $value): int
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
