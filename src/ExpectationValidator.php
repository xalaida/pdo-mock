<?php

namespace Xalaida\PDOMock;

use RuntimeException;

class ExpectationValidator
{
    public $expectations = [];

    /**
     * @var AssertionManagerInterface
     */
    public $assertionManager;

    public function __construct($assertionManager)
    {
        $this->assertionManager = $assertionManager;
    }

    public function expectQuery($query)
    {
        $expectation = new Expectation($query);

        $this->expectations[] = $expectation;

        return $expectation;
    }

    public function expectFunction($function)
    {
        $expectation = new Expectation($function);

        $this->expectations[] = $expectation;

        return $expectation;
    }

    /**
     * @param string $query
     * @return Expectation
     * @throws \UnexpectedValueException
     */
    public function getExpectationForQuery($query)
    {
        if (empty($this->expectations)) {
            throw new \RuntimeException('Unexpected query: ' . $query);
        }

        return array_shift($this->expectations);
    }

    public function getExpectationForFunction($function)
    {
        if (empty($this->expectations)) {
            throw new \RuntimeException('Unexpected function: ' . $function);
        }

        return array_shift($this->expectations);
    }

    public function validateQuery($expectation, $reality)
    {
        if ($expectation !== $reality) {
            throw new RuntimeException('Unexpected query: ' . $reality);
        }
    }

    public function validateParams()
    {

    }

    /**
     * @return void
     */
    public function assertExpectationsFulfilled()
    {
        $this->assertionManager->increment();

        if (! empty($this->expectations)) {
            throw new RuntimeException('Some expectations were not fulfilled.');
        }
    }
}
