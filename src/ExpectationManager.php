<?php

namespace Xalaida\PDOMock;

class ExpectationManager
{
    /**
     * @var array<int, QueryExpectation|FunctionExpectation>
     */
    protected $expectations = [];

    /**
     * @param string $query
     * @return QueryExpectation
     */
    public function pushQueryExpectation($query)
    {
        $expectation = new QueryExpectation(
            PDOMock::$expectationValidator ?: new ExpectationValidator(), $query
        );

        $this->expectations[] = $expectation;

        return $expectation;
    }

    /**
     * @param string $query
     * @return QueryExpectation
     * @throws ExpectationFailedException
     */
    public function pullQueryExpectation($query)
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
     */
    public function pushFunctionExpectation($function)
    {
        $expectation = new FunctionExpectation(
            PDOMock::$expectationValidator ?: new ExpectationValidator(), $function
        );

        $this->expectations[] = $expectation;

        return $expectation;
    }

    /**
     * @param string $function
     * @return FunctionExpectation
     * @throws ExpectationFailedException
     */
    public function pullFunctionExpectation($function)
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
