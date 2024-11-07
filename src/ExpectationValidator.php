<?php

namespace Xalaida\PDOMock;

class ExpectationValidator implements ExpectationValidatorInterface
{
    /**
     * @var QueryMatcherInterface|null
     */
    protected $queryMatcher;

    public function setQueryMatcher($queryMatcher)
    {
        $this->queryMatcher = $queryMatcher;
    }

    public function assertQueryMatch($expectation, $reality)
    {
        if (! $this->queryMatcher->match($expectation, $reality)) {
            throw new ExpectationFailedException('Unexpected query: ' . $reality);
        }
    }

    public function assertParamsMatch($expectation, $reality)
    {
        if (is_callable($expectation)) {
            $result = call_user_func($expectation, $reality);

            if ($result === false) {
                throw new ExpectationFailedException('Params do not match.');
            }
        } else {
            if ($expectation != $reality) {
                throw new ExpectationFailedException('Params do not match.');
            }
        }
    }

    public function assertIsPrepared($reality)
    {
        if ($reality === false) {
            throw new ExpectationFailedException('Statement is not prepared.');
        }
    }

    public function assertIsNotPrepared($reality)
    {
        if ($reality === true) {
            throw new ExpectationFailedException('Statement is prepared.');
        }
    }

    public function assertFunctionMatch($expectation, $reality)
    {
        if ($expectation !== $reality) {
            throw new ExpectationFailedException('Unexpected function: ' . $reality);
        }
    }
}
