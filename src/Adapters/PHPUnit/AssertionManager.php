<?php

namespace Xalaida\PDOMock\Adapters\PHPUnit;

use PHPUnit\Framework\TestCase;
use Xalaida\PDOMock\AssertionManager as AssertionManagerInterface;

class AssertionManager implements AssertionManagerInterface
{
    /**
     * @var TestCase
     */
    public $phpunit;

    public function __construct($phpunit)
    {
        $this->phpunit = $phpunit;
    }

    public function incrementAssertions($count = 1)
    {
        $this->phpunit->addToAssertionCount($count);
    }
}
