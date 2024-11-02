<?php

namespace Xalaida\PDOMock;

class FunctionExpectation
{
    /**
     * @var AssertionValidator
     */
    public $assertionValidator;

    /**
     * @var string
     */
    public $function;

    /**
     * @param string $function
     */
    public function __construct($function)
    {
        $this->function = $function;
    }

    /**
     * @param AssertionValidator $assertionValidator
     * @return void
     */
    public function setAssertionValidator($assertionValidator)
    {
        $this->assertionValidator = $assertionValidator;
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
