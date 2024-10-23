<?php

namespace Xala\Elomock;

use Override;
use PDO;
use PHPUnit\Framework\TestCase;

class FakePDO extends PDO
{
    /**
     * @var array<int, QueryExpectation>
     */
    public array $expectations = [];

    /**
     * @var array<int, int>
     */
    public array $attributes = [];

    /**
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct()
    {
        $this->attributes = [
            // TODO: define missing attributes
            self::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_BOTH,
        ];
    }

    #[Override]
    public function setAttribute($attribute, $value)
    {
        $this->attributes[$attribute] = $value;

        return true;
    }

    #[Override]
    public function getAttribute($attribute)
    {
        // TODO: handle unknown attributes

        return $this->attributes[$attribute];
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
