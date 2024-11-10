<?php

namespace Xalaida\PDOMock;

class ExpectationValidator implements ExpectationValidatorInterface
{
    /**
     * @inheritDoc
     */
    public function assertQueryMatch($expectation, $reality)
    {
        if (! $expectation->compareQuery($reality)) {
            throw new ExpectationFailedException('Unexpected query: ' . $reality);
        }
    }

    /**
     * @inheritDoc
     */
    public function assertParamsMatch($expectation, $params, $types)
    {
        if (! $expectation->compareParams($params, $types)) {
            throw new ExpectationFailedException('Params do not match.');
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
        if ($expectation->function !== $reality) {
            throw new ExpectationFailedException('Unexpected function: ' . $reality);
        }
    }
}
