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
    protected PDOMock $pdo;

    protected Expectation $expectation;

    public string $queryString;

    public array $bindings = [];

    protected int $fetchMode;

    protected int $cursor = 0;

    protected array $errorInfo;

    protected string | null $errorCode;

    private bool $executed;

    public function __construct(PDOMock $pdo, Expectation $expectation, string $query)
    {
        $this->pdo = $pdo;
        $this->expectation = $expectation;
        $this->queryString = $query;
        $this->executed = false;
        $this->errorInfo = ['', null, null];
        $this->errorCode = null;
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

        TestCase::assertSame($this->expectation->query, $this->queryString, 'Query does not match');

        if (! is_null($this->expectation->bindings)) {
            if (is_callable($this->expectation->bindings)) {
                $result = call_user_func($this->expectation->bindings, $bindings);

                TestCase::assertNotFalse($result, 'Bindings do not match');
            } else {
                TestCase::assertEquals($this->expectation->bindings, $bindings, 'Bindings do not match');
            }
        }

        $this->executed = true;

        if ($this->expectation->exceptionOnExecute && $this->expectation->exceptionOnExecute->errorInfo) {
            $this->errorInfo = $this->expectation->exceptionOnExecute->errorInfo;
            $this->errorCode = $this->expectation->exceptionOnExecute->errorInfo[0];
        } else {
            $this->errorInfo = ['00000', null, null];
            $this->errorCode = $this->errorInfo[0];
        }

        if ($this->expectation->exceptionOnExecute) {
            if ($this->pdo->getAttribute($this->pdo::ATTR_ERRMODE) === $this->pdo::ERRMODE_SILENT) {
                return false;
            }

            if ($this->pdo->getAttribute($this->pdo::ATTR_ERRMODE) === $this->pdo::ERRMODE_WARNING) {
                trigger_error('PDOStatement::execute(): ' . $this->expectation->exceptionOnExecute->getMessage(), E_USER_WARNING);

                return false;
            }

            if ($this->pdo->getAttribute($this->pdo::ATTR_ERRMODE) === $this->pdo::ERRMODE_EXCEPTION) {
                throw $this->expectation->exceptionOnExecute;
            }
        }

        if (! is_null($this->expectation->insertId)) {
            $this->pdo->lastInsertId = $this->expectation->insertId;
        }

        return true;
    }

    #[Override]
    public function rowCount(): int
    {
        if (! $this->executed) {
            return 0;
        }

        return $this->expectation->rowCount;
    }

    public function errorCode(): ?string
    {
        return $this->errorCode;
    }

    public function errorInfo(): array
    {
        return $this->errorInfo;
    }

    #[Override]
    public function fetch($mode = PDO::FETCH_DEFAULT, $cursorOrientation = PDO::FETCH_ORI_NEXT, $cursorOffset = 0)
    {
        if (! $this->executed) {
            return false;
        }

        if (isset($this->expectation->rows[$this->cursor])) {
            $row = $this->applyFetchMode($this->expectation->rows[$this->cursor], $mode);

            $this->cursor++;

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
