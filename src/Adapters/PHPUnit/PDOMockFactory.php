<?php

namespace Xalaida\PDOMock\Adapters\PHPUnit;

use PHPUnit\Framework\TestCase;
use Xalaida\PDOMock\PDOMock;
use Xalaida\PDOMock\AssertionValidator as BaseAssertionValidator;

class PDOMockFactory
{
    /**
     * @param TestCase $phpunit
     * @return PDOMock
     */
    public static function forTestCase($phpunit)
    {
        $pdo = new PDOMock();

        $assertionValidator = new AssertionValidator($phpunit, new BaseAssertionValidator());

        $pdo->expectationManager->setAssertionValidator($assertionValidator);

        return $pdo;
    }
}
