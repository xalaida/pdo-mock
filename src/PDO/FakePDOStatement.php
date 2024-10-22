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

        TestCase::assertEquals($expectation->query, $this->query);
        TestCase::assertEquals($expectation->bindings, $this->bindings);

        return 1;
    }
}
