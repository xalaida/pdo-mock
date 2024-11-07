<?php

namespace Xalaida\PDOMock;

interface ExpectationValidatorInterface
{
    /**
     * @param QueryMatcherInterface $queryMatcher
     * @return void
     */
    public function setQueryMatcher($queryMatcher);

    /**
     * @param string $expectation
     * @param string $reality
     * @return void
     */
    public function assertQueryMatch($expectation, $reality);

    /**
     * @param string $expectation
     * @param string $reality
     * @return void
     */
    public function assertParamsMatch($expectation, $reality);

    /**
     * @param bool $reality
     * @return void
     */
    public function assertIsPrepared($reality);

    /**
     * @param bool $reality
     * @return void
     */
    public function assertIsNotPrepared($reality);

    /**
     * @param string $expectation
     * @param string $reality
     * @return void
     */
    public function assertFunctionMatch($expectation, $reality);
}
