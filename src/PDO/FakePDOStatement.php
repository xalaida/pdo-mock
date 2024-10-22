<?php

namespace Xala\Elomock\PDO;

use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class FakePDOStatement extends PDOStatement
{
    private FakePDO $pdo;

    public string $query;

    public array $bindings = [];

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

    public function execute($params = [])
    {
        $expectation = array_shift($this->pdo->expectations);

        if (!empty($params)) {
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

        TestCase::assertEquals($expectation->query, $this->query);
        TestCase::assertEquals($expectation->bindings, $bindings);

        return 1;
    }
}
