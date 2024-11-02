<?php

namespace Xalaida\PDOMock\Adapters\PHPUnit;

use PHPUnit\Framework\TestCase;
use Xalaida\PDOMock\AssertionValidatorInterface;

class AssertionValidator implements AssertionValidatorInterface
{
    /**
     * @var TestCase
     */
    public $phpunit;

    /**
     * @var AssertionValidator
     */
    public $assertionValidator;

    /**
     * @param TestCase $phpunit
     * @param AssertionValidatorInterface $assertionValidator
     */
    public function __construct($phpunit, $assertionValidator)
    {
        $this->phpunit = $phpunit;
        $this->assertionValidator = $assertionValidator;
    }

    /**
     * @inheritDoc
     */
    public function assertQueryMatch($expectation, $reality)
    {
        $this->phpunit->addToAssertionCount(1);

        $this->assertionValidator->assertQueryMatch($expectation, $reality);
    }

    /**
     * @inheritDoc
     */
    public function assertParamsMatch($expectation, $reality)
    {
        $this->phpunit->addToAssertionCount(1);

        $this->assertionValidator->assertParamsMatch($expectation, $reality);
    }

    /**
     * @inheritDoc
     */
    public function assertIsPrepared($reality)
    {
        $this->phpunit->addToAssertionCount(1);

        $this->assertionValidator->assertIsPrepared($reality);
    }

    /**
     * @inheritDoc
     */
    public function assertIsNotPrepared($reality)
    {
        $this->phpunit->addToAssertionCount(1);

        $this->assertionValidator->assertIsNotPrepared($reality);
    }

    /**
     * @inheritDoc
     */
    public function assertFunctionMatch($expectation, $reality)
    {
        $this->phpunit->addToAssertionCount(1);

        $this->assertionValidator->assertFunctionMatch($expectation, $reality);
    }
}
