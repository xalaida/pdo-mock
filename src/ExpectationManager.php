<?php

namespace Xalaida\PDOMock;

class ExpectationManager
{
    /**
     * @var AssertionValidatorInterface
     */
    public static $assertionValidator;

    /**
     * @var array<int, QueryExpectation|FunctionExpectation>
     */
    public $expectations = [];

    /**
     * @param string $query
     * @return QueryExpectation
     */
    public function expectQuery($query)
    {
        $expectation = new QueryExpectation(static::getAssertionValidator(), $query);

        $this->expectations[] = $expectation;

        return $expectation;
    }

    /**
     * @param string $function
     * @return FunctionExpectation
     */
    public function expectFunction($function)
    {
        $expectation = new FunctionExpectation(static::getAssertionValidator(), $function);

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

    /**
     * @param AssertionValidatorInterface $assertionValidator
     */
    public static function useAssertionValidator($assertionValidator)
    {
        static::$assertionValidator = $assertionValidator;
    }

    /**
     * @return AssertionValidatorInterface
     */
    protected static function getAssertionValidator()
    {
        if (is_null(static::$assertionValidator)) {
            static::$assertionValidator = new AssertionValidator();
        }

        return static::$assertionValidator;
    }
}
