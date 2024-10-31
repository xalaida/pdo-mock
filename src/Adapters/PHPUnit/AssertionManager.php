<?php

namespace Xalaida\PDOMock\Adapters\PHPUnit;

use PHPUnit\Framework\TestCase;
use Xalaida\PDOMock\AssertionManagerInterface as AssertionManagerInterface;

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

    public function increment($count = 1)
    {
        $this->phpunit->addToAssertionCount($count);
    }
}
