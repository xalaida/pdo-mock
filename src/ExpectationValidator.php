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

    public function verifyStatement($expectation, $statement)
    {
        $this->assertQueryEquals($expectation, $statement);
        $this->validateParams($expectation, $statement);
        $this->validatePrepared($expectation, $statement);
    }

    public function assertQueryMatch($expectation, $reality)
    {
        $this->assertionManager->increment();

        if ($expectation !== $reality) {
            throw new RuntimeException('Unexpected query: ' . $reality);
        }
    }

    public function assertParamsEqual($expectation, $reality)
    {
        $this->assertionManager->increment();

        if (! is_null($expectation)) {
            if (is_callable($expectation)) {
                $result = call_user_func($expectation, $reality);

                if ($result === false) {
                    throw new RuntimeException('Params do not match');
                }
            } else {
                if ($expectation != $reality) {
                    throw new RuntimeException('Params do not match');
                }
            }
        }
    }

    public function assertPreparedMatch($expectation, $reality)
    {
        if ($expectation !== null) {
            $this->assertionManager->increment();
        }

        if ($expectation === true && $reality === false) {
            throw new RuntimeException('Statement is not prepared');
        }

        if ($expectation === false && $reality === true) {
            throw new RuntimeException('Statement should not be prepared');
        }
    }

    public function assertIsNotPrepared($expectation, $reality)
    {
        $this->assertionManager->increment();

        if ($expectation === true && $reality === false) {
            throw new RuntimeException('Statement is not prepared');
        }

        if ($expectation === false && $reality === true) {
            throw new RuntimeException('Statement should not be prepared');
        }
    }

    public function assertFunctionIsExpected($function)
    {
        $expectation = $this->getExpectationForFunction($function);

        $this->assertionManager->increment();

        if ($expectation->query !== $function) {
            throw new \RuntimeException('Unexpected function: ' . $function);
        }
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
