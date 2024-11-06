<?php

namespace Xalaida\PDOMock;

class AssertionValidator implements AssertionValidatorInterface
{
    public function assertQueryMatch($result)
    {
        if (! $result) {
            // TODO: rewrite this.
            throw new ExpectationFailedException('Unexpected query: ');
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
