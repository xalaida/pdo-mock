<?php

namespace Xala\Elomock\PDO;

use PDO;
use PHPUnit\Framework\TestCase;

/**
 * @todo handle bindColumn
 * @todo handle prefix :placeholder
 */
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
        // parent::exec();

        $expectation = array_shift($this->expectations);

        TestCase::assertFalse($expectation->prepared);

        TestCase::assertEquals($expectation->query, $statement);

        return 1;
    }

    public function prepare($query, $options = [])
    {
        // parent::prepare();

        return new FakePDOStatement($this, $query);
    }
}
