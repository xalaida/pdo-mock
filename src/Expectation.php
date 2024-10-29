<?php

namespace Xala\Elomock;

use Closure;
use InvalidArgumentException;
use PDO;
use PDOException;

class Expectation
{
    public PDO $pdo;

    public ?PDOStatementMock $statement = null;

    public string $query;

    public array | Closure | null $params = null;

    public bool $executed = true;

    public bool | null $prepared = null;

    public int $rowCount = 0;

    public ?ResultSet $resultSet = null;

    public ?string $insertId = null;

    public ?PDOException $exceptionOnExecute = null;

    public ?PDOException $exceptionOnPrepare = null;

    public function __construct(PDO $pdo, string $query)
    {
        $this->pdo = $pdo;
        $this->query = $query;
    }

    public function toBeExecuted(bool $executed = true): static
    {
        $this->executed = $executed;

        return $this;
    }

    public function toBePrepared(bool $prepared = true): static
    {
        $this->prepared = $prepared;

        return $this;
    }

    public function withParam(string | int $param, mixed $value, int $type = PDO::PARAM_STR): static
    {
        $this->params[$param] = [
            'value' => $value,
            'type' => $type,
        ];

        return $this;
    }

    public function with(array | Closure $params, bool $useParamValueType = false): static
    {
        if (is_callable($params)) {
            $this->params = $params;

            return $this;
        }

        foreach ($params as $key => $value) {
            $param = is_int($key)
                ? $key + 1
                : $key;

            $type = $useParamValueType
                ? $this->getTypeFromValue($value)
                : PDO::PARAM_STR;

            $this->withParam($param, $value, $type);
        }

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

    public function andFetch(ResultSet $resultSet): static
    {
        $this->resultSet = $resultSet;

        return $this;
    }

    public function andFetchRows(array $rows): static
    {
        return $this->andFetch(
            ResultSet::fromArray($rows),
        );
    }

    public function andFetchRecord(array $row): static
    {
        return $this->andFetch(
            ResultSet::fromArray([
                $row,
            ]),
        );
    }

    public function andFailOnExecute(PDOException $exception): static
    {
        $this->exceptionOnExecute = $exception;

        return $this;
    }

    public function andFailOnPrepare(PDOException $exception): static
    {
        $this->exceptionOnPrepare = $exception;

        return $this;
    }

    public function then(): PDO
    {
        return $this->pdo;
    }

    protected function getTypeFromValue(mixed $value): int
    {
        $type = gettype($value);

        switch ($type) {
            case 'string':
                return PDO::PARAM_STR;

            case 'integer':
                return PDO::PARAM_INT;

            case 'boolean':
                return PDO::PARAM_BOOL;

            default:
                throw new InvalidArgumentException('Unsupported type: ' . $type);
        }
    }
}
