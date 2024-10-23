<?php

namespace Xala\Elomock;

use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class FakePDOStatement extends PDOStatement
{
    protected FakePDO $pdo;

    public string $query;

    public array $bindings = [];

    protected ?QueryExpectation $expectation = null;

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

        return $this->expectation->rows;
    }
}
