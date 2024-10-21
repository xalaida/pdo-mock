<?php

namespace Xala\Elomock\PDO;

use PDO;
use PHPUnit\Framework\TestCase;

class FakePDO extends PDO
{
    public array $expectations = [];

    /**
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct()
    {
        //
    }

    public function expectQuery(string $query): QueryExpectation
    {
        $expectation = new QueryExpectation($query);

        $this->expectations[] = $expectation;

        return $expectation;
    }

    public function exec($statement)
    {
        $expectation = array_shift($this->expectations);

        TestCase::assertEquals($expectation->query, $statement);

        return 1;
    }



//    public function exec(string $statement): int|false
//    {
//
//    }
}
