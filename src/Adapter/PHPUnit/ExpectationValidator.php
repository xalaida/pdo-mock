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
            $expectation->compareQuery($reality),
            'Query does not match.'
        );
    }

    /**
     * @inheritDoc
     */
    public function assertParamsMatch($expectation, $params, $types)
    {
        PHPUnit::assertTrue(
            $expectation->compareParams($params, $types),
            'Params do not match.'
        );
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
        PHPUnit::assertEquals($expectation->function, $reality, 'Function does not match');
    }
}
