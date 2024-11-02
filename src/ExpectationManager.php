<?php

namespace Xalaida\PDOMock;

class ExpectationManager
{
    /**
     * @var array<int, QueryExpectation|FunctionExpectation>
     */
    public $expectations = [];

    /**
     * @var AssertionValidator
     */
    public $assertionValidator;

    public function __construct()
    {
        $this->assertionValidator = new AssertionValidator();
    }

    /**
     * @param AssertionValidator $assertionValidator
     * @return void
     */
    public function setAssertionValidator($assertionValidator)
    {
        $this->assertionValidator = $assertionValidator;
    }

    /**
     * @param string $query
     * @return QueryExpectation
     */
    public function expectQuery($query)
    {
        $expectation = new QueryExpectation($query);

        $expectation->setAssertionValidator($this->assertionValidator);

        $this->expectations[] = $expectation;

        return $expectation;
    }

    /**
     * @param string $function
     * @return FunctionExpectation
     */
    public function expectFunction($function)
    {
        $expectation = new FunctionExpectation($function);

        $expectation->setAssertionValidator($this->assertionValidator);

        $this->expectations[] = $expectation;

        return $expectation;
    }

    /**
     * @param string $query
     * @return QueryExpectation
     * @throws ExpectationFailedException
     */
    public function getExpectationForQuery($query)
    {
        if (empty($this->expectations)) {
            throw new ExpectationFailedException('Unexpected query: ' . $query);
        }

        $expectation = array_shift($this->expectations);

        if (! $expectation instanceof QueryExpectation) {
            throw new ExpectationFailedException('Unexpected query: ' . $query);
        }

        return $expectation;
    }

    /**
     * @param string $function
     * @return FunctionExpectation
     * @throws ExpectationFailedException
     */
    public function getExpectationForFunction($function)
    {
        if (empty($this->expectations)) {
            throw new ExpectationFailedException('Unexpected function: ' . $function);
        }

        $expectation = array_shift($this->expectations);

        if (! $expectation instanceof FunctionExpectation) {
            throw new ExpectationFailedException('Unexpected function: ' . $function);
        }

        return $expectation;
    }

    /**
     * @return void
     */
    public function assertExpectationsFulfilled()
    {
        if (! empty($this->expectations)) {
            throw new ExpectationFailedException('Some expectations were not fulfilled.');
        }
    }
}
