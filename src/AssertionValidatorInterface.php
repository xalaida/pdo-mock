<?php

namespace Xalaida\PDOMock;

interface AssertionValidatorInterface
{
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
