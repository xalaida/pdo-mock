<?php

namespace Xala\Elomock;

use InvalidArgumentException;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use ValueError;

class FakePDOStatement extends PDOStatement
{
    public string $queryString;

    protected int $fetchMode;

    protected FakePDO $pdo;

    protected array $bindings = [];

    // TODO: consider passing this with constructor
    protected ?QueryExpectation $expectation = null;

    protected int $cursor = 0;

    protected bool $executed = false;

    public function __construct(FakePDO $pdo, string $query)
    {
        $this->queryString = $query;
        $this->pdo = $pdo;
    }

    public function setFetchMode($mode, $className = null, ...$params)
    {
        $this->fetchMode = $mode;
    }

    public function bindValue($param, $value, $type = PDO::PARAM_STR)
    {
        $this->bindings[$param] = [
            'value' => $value,
            'type' => $type
        ];

        return true;
    }

    public function bindParam($param, &$var, $type = PDO::PARAM_STR, $maxLength = 0, $driverOptions = null)
    {
        $this->bindings[$param] = [
            'value' => $var,
            'type' => $type
        ];

        return true;
    }

    public function execute(?array $params = null)
    {
        TestCase::assertNotEmpty($this->pdo->expectations, 'Unexpected query: ' . $this->queryString);

        $this->expectation = array_shift($this->pdo->expectations);

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
        TestCase::assertEquals($this->expectation->bindings, $bindings, 'Bindings do not match');

        if ($this->expectation->exception) {
            throw $this->expectation->exception;
        }

        $this->executed = true;

        if (! is_null($this->expectation->insertId)) {
            $this->pdo->lastInsertId = $this->expectation->insertId;
        }

        return true;
    }

    public function rowCount()
    {
        if (is_null($this->expectation)) {
            return 0;
        }

        return $this->expectation->rowCount;
    }

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
