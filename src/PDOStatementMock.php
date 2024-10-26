<?php

namespace Xala\Elomock;

use InvalidArgumentException;
use Override;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use ValueError;

class PDOStatementMock extends PDOStatement
{
    public string $queryString;

    public array $bindings = [];

    protected int $fetchMode;

    protected PDOMock $pdo;

    // TODO: consider passing this with constructor
    protected ?Expectation $expectation = null;

    protected int $cursor = 0;

    protected bool $executed = false;

    public function __construct(PDOMock $pdo, string $query)
    {
        $this->queryString = $query;
        $this->pdo = $pdo;
    }

    #[Override]
    public function setFetchMode($mode, $className = null, ...$params): void
    {
        $this->fetchMode = $mode;
    }

    #[Override]
    public function bindValue($param, $value, $type = PDO::PARAM_STR): bool
    {
        $this->bindings[$param] = [
            'value' => $value,
            'type' => $type
        ];

        return true;
    }

    #[Override]
    public function bindParam($param, &$var, $type = PDO::PARAM_STR, $maxLength = 0, $driverOptions = null): bool
    {
        $this->bindings[$param] = [
            'value' => $var,
            'type' => $type
        ];

        return true;
    }

    #[Override]
    public function execute(?array $params = null): bool
    {
        TestCase::assertNotEmpty($this->pdo->expectations, 'Unexpected query: ' . $this->queryString);

        $this->expectation = array_shift($this->pdo->expectations);

        $this->expectation->statement = $this;

        if (! is_null($params)) {
            $bindings = [];

            foreach ($params as $key => $value) {
                $param = is_int($key)
                    ? $key + 1
                    : $key;

                $bindings[$param] = [
                    'value' => $value,
                    'type' => PDO::PARAM_STR,
                ];
            }
        } else {
            $bindings = $this->bindings;
        }

        if (! is_null($this->expectation->prepared)) {
            TestCase::assertTrue($this->expectation->prepared, 'Statement is not prepared');
        }

        TestCase::assertEquals($this->expectation->query, $this->queryString, 'Query does not match');

        if (! is_null($this->expectation->bindings)) {
            if (is_callable($this->expectation->bindings)) {
                $result = call_user_func($this->expectation->bindings, $bindings);

                TestCase::assertNotFalse($result, 'Bindings do not match');
            } else {
                TestCase::assertEquals($this->expectation->bindings, $bindings, 'Bindings do not match');
            }
        }

        if ($this->expectation->exception) {
            throw $this->expectation->exception;
        }

        $this->executed = true;

        if (! is_null($this->expectation->insertId)) {
            $this->pdo->lastInsertId = $this->expectation->insertId;
        }

        return true;
    }

    #[Override]
    public function rowCount(): int
    {
        if (is_null($this->expectation)) {
            return 0;
        }

        return $this->expectation->rowCount;
    }

    public function errorCode(): ?string
    {
        if (! $this->executed) {
            return null;
        }

        return '00000';
    }

    public function errorInfo(): array
    {
        return [$this->errorCode(), null, null];
    }

    #[Override]
    public function fetch($mode = PDO::FETCH_DEFAULT, $cursorOrientation = PDO::FETCH_ORI_NEXT, $cursorOffset = 0)
    {
        // TODO: ensure statement is executed

        if (isset($this->expectation->rows[$this->cursor])) {
            $row = $this->applyFetchMode($this->expectation->rows[$this->cursor], $mode);

            $this->cursor += 1;

            return $row;
        }

        return false;
    }

    #[Override]
    public function fetchAll($mode = PDO::FETCH_DEFAULT, ...$args)
    {
        if ($mode === PDO::FETCH_LAZY) {
            throw new ValueError('PDOStatement::fetchAll(): Argument #1 ($mode) cannot be PDO::FETCH_LAZY in PDOStatement::fetchAll()');
        }

        if (! $this->executed) {
            return [];
        }

        return array_map(function ($row) use ($mode) {
            return $this->applyFetchMode($row, $mode);
        }, $this->expectation->rows);
    }

    protected function applyFetchMode(array $row, int $mode): object | array
    {
        if ($mode === PDO::FETCH_DEFAULT) {
            $mode = $this->fetchMode;
        }

        switch ($mode) {
            case PDO::FETCH_ASSOC:
                return (array) $row;

            case PDO::FETCH_NUM:
                return array_values($row);

            case PDO::FETCH_OBJ:
                return (object) $row;

            case PDO::FETCH_BOTH:
                return array_merge($row, array_values($row));

            default:
                throw new InvalidArgumentException("Unsupported fetch mode: " . $mode);
        }
    }
}
