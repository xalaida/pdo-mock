<?php

namespace Xala\Elomock;

use InvalidArgumentException;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class FakePDOStatement extends PDOStatement
{
    protected FakePDO $pdo;

    public string $query;

    public array $bindings = [];

    // TODO: consider passing this with constructor
    protected ?QueryExpectation $expectation = null;

    protected int $cursor = 0;

    public function __construct(FakePDO $pdo, string $query)
    {
        $this->query = $query;
        $this->pdo = $pdo;
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

        TestCase::assertTrue($this->expectation->prepared);
        TestCase::assertEquals($this->expectation->query, $this->query);
        TestCase::assertEquals($this->expectation->bindings, $bindings);

        return 1;
    }

    public function fetchAll($mode = PDO::FETCH_DEFAULT, ...$args)
    {
        // TODO: ensure statement is executed

        return array_map(function ($row) use ($mode) {
            return $this->applyFetchMode($row, $mode);
        }, $this->expectation->rows);
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

    protected function applyFetchMode(array $row, int $fetchMode): object | array
    {
        switch ($fetchMode) {
            case PDO::FETCH_ASSOC:
                return (array) $row;

            case PDO::FETCH_NUM:
                return array_values($row);

            case PDO::FETCH_OBJ:
                return (object) $row;

            default:
                throw new InvalidArgumentException("Unsupported fetch mode: " . $fetchMode);
        }
    }
}
