<?php

namespace Xalaida\PDOMock\Adapter\PHPUnit;

use PHPUnit\Framework\TestCase;
use Xalaida\PDOMock\AssertionValidatorInterface;

class AssertionValidator implements AssertionValidatorInterface
{
    public function assertQueryMatch($expectation, $reality)
    {
        TestCase::assertEquals($expectation, $reality, 'Query does not match.');
    }

    public function assertParamsMatch($expectation, $reality)
    {
        if (is_callable($expectation)) {
            $result = call_user_func($expectation, $reality);

            TestCase::assertNotFalse($result, 'Params do not match.');
        } else {
            TestCase::assertEquals($expectation, $reality, 'Params do not match.');
        }
    }

    public function assertIsPrepared($reality)
    {
        TestCase::assertTrue($reality, 'Statement is not prepared.');
    }

    public function assertIsNotPrepared($reality)
    {
        TestCase::assertFalse($reality, 'Statement is prepared.');
    }

    public function assertFunctionMatch($expectation, $reality)
    {
        TestCase::assertEquals($expectation, $reality, 'Function does not match');
    }
}
