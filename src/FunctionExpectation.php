<?php

namespace Xalaida\PDOMock;

class FunctionExpectation
{
    /**
     * @var AssertionValidatorInterface
     */
    public $assertionValidator;

    /**
     * @var string
     */
    public $function;

    /**
     * @param AssertionValidatorInterface $assertionValidator
     * @param string $function
     */
    public function __construct($assertionValidator, $function)
    {
        $this->assertionValidator = $assertionValidator;
        $this->function = $function;
    }

    /**
     * @param string $function
     * @return void
     */
    public function assertFunctionMatch($function)
    {
        $this->assertionValidator->assertFunctionMatch($this->function, $function);
    }
}
