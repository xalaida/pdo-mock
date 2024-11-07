<?php

namespace Xalaida\PDOMock;

class ExpectationValidator implements ExpectationValidatorInterface
{
    /**
     * @inheritDoc
     */
    public function assertQueryMatch($expectation, $reality)
    {
        if (! $expectation->queryMatcher->match($expectation->query, $reality)) {
            throw new ExpectationFailedException('Unexpected query: ' . $reality);
        }
    }

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function assertIsPrepared($reality)
    {
        if ($reality === false) {
            throw new ExpectationFailedException('Statement is not prepared.');
        }
    }

    /**
     * @inheritDoc
     */
    public function assertIsNotPrepared($reality)
    {
        if ($reality === true) {
            throw new ExpectationFailedException('Statement is prepared.');
        }
    }

    /**
     * @inheritDoc
     */
    public function assertFunctionMatch($expectation, $reality)
    {
        if ($expectation !== $reality) {
            throw new ExpectationFailedException('Unexpected function: ' . $reality);
        }
    }
}
