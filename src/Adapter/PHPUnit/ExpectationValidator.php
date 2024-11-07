<?php

namespace Xalaida\PDOMock\Adapter\PHPUnit;

use Xalaida\PDOMock\ExpectationValidatorInterface;
use PHPUnit\Framework\Assert as PHPUnit;

class ExpectationValidator implements ExpectationValidatorInterface
{
    /**
     * @inheritDoc
     */
    public function assertQueryMatch($expectation, $reality)
    {
        PHPUnit::assertTrue(
            $expectation->queryMatcher->match($expectation->query, $reality),
            'Query does not match.'
        );
    }

    /**
     * @inheritDoc
     */
    public function assertParamsMatch($expectation, $reality)
    {
        if (is_callable($expectation)) {
            $result = call_user_func($expectation, $reality);

            PHPUnit::assertNotFalse($result, 'Params do not match.');
        } else {
            PHPUnit::assertEquals($expectation, $reality, 'Params do not match.');
        }
    }

    /**
     * @inheritDoc
     */
    public function assertIsPrepared($reality)
    {
        PHPUnit::assertTrue($reality, 'Statement is not prepared.');
    }

    /**
     * @inheritDoc
     */
    public function assertIsNotPrepared($reality)
    {
        PHPUnit::assertFalse($reality, 'Statement is prepared.');
    }

    /**
     * @inheritDoc
     */
    public function assertFunctionMatch($expectation, $reality)
    {
        PHPUnit::assertEquals($expectation, $reality, 'Function does not match');
    }
}
