<?php

namespace Xalaida\PDOMock;

class FunctionExpectation
{
    /**
     * @var ExpectationValidatorInterface
     */
    public $expectationValidator;

    /**
     * @var string
     */
    public $function;

    /**
     * @param ExpectationValidatorInterface $expectationValidator
     * @param string $function
     */
    public function __construct($expectationValidator, $function)
    {
        $this->expectationValidator = $expectationValidator;
        $this->function = $function;
    }

    /**
     * @param string $function
     * @return void
     */
    public function assertFunctionMatch($function)
    {
        $this->expectationValidator->assertFunctionMatch($this, $function);
    }
}
