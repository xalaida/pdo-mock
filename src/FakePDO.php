<?php

namespace Xala\Elomock;

use PDO;
use PHPUnit\Framework\TestCase;

class FakePDO extends PDO
{
    /**
     * @var array<int, QueryExpectation>
     */
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

        TestCase::assertFalse($expectation->prepared);

        TestCase::assertEquals($expectation->query, $statement);

        return true;
    }

    public function prepare($query, $options = [])
    {
        // TODO: pass expectation to statement...

        return new FakePDOStatement($this, $query);
    }
}
