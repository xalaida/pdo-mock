<?php

namespace Xalaida\PDOMock;

use RuntimeException;

class ExpectationValidator
{
    public $expectations = [];

    public $callbacks = [];

    /**
     * @return void
     */
    public function useCallback($callback)
    {
        $this->callbacks[] = $callback;
    }

    /**
     * @return void
     */
    protected function runCallbacks()
    {
        foreach ($this->callbacks as $callback) {
            $callback();
        }
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

    public function assertQueryMatch($expectation, $reality)
    {
        $this->runCallbacks();

        if ($expectation !== $reality) {
            throw new RuntimeException('Unexpected query: ' . $reality);
        }
    }

    public function assertParamsEqual($expectation, $reality)
    {
        $this->runCallbacks();

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
            $this->runCallbacks();
        }

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

        $this->runCallbacks();

        if ($expectation->query !== $function) {
            throw new \RuntimeException('Unexpected function: ' . $function);
        }
    }

    /**
     * @return void
     */
    public function assertExpectationsFulfilled()
    {
        $this->runCallbacks();

        if (! empty($this->expectations)) {
            throw new RuntimeException('Some expectations were not fulfilled.');
        }
    }
}
