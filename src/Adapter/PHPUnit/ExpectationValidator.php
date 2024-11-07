<?php

namespace Xalaida\PDOMock\Adapter\PHPUnit;

use Xalaida\PDOMock\ExpectationValidatorInterface;
use Xalaida\PDOMock\PDOMock;
use Xalaida\PDOMock\QueryMatcherInterface;
use PHPUnit\Framework\Assert as PHPUnit;

class ExpectationValidator implements ExpectationValidatorInterface
{
    /**
     * @var QueryMatcherInterface
     */
    protected $queryMatcher;

    public function setQueryMatcher($queryMatcher)
    {
        $this->queryMatcher = $queryMatcher;
    }

    public function getQueryMatcher()
    {
        return $this->queryMatcher ?: PDOMock::getDefaultQueryMatcher();
    }

    public function assertQueryMatch($expectation, $reality)
    {
        PHPUnit::assertTrue(
            $this->getQueryMatcher()->match($expectation, $reality),
            'Query does not match.'
        );
    }

    public function assertParamsMatch($expectation, $reality)
    {
        if (is_callable($expectation)) {
            $result = call_user_func($expectation, $reality);

            PHPUnit::assertNotFalse($result, 'Params do not match.');
        } else {
            PHPUnit::assertEquals($expectation, $reality, 'Params do not match.');
        }
    }

    public function assertIsPrepared($reality)
    {
        PHPUnit::assertTrue($reality, 'Statement is not prepared.');
    }

    public function assertIsNotPrepared($reality)
    {
        PHPUnit::assertFalse($reality, 'Statement is prepared.');
    }

    public function assertFunctionMatch($expectation, $reality)
    {
        PHPUnit::assertEquals($expectation, $reality, 'Function does not match');
    }
}
