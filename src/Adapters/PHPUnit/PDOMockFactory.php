<?php

namespace Xalaida\PDOMock\Adapters\PHPUnit;

use Xalaida\PDOMock\PDOMock;

class PDOMockFactory
{
    public static function forTestCase($phpunit)
    {
        $pdo = new PDOMock();

        $assertionManager = new AssertionManager($phpunit);

        $pdo->setAssertionManager($assertionManager);

        return $pdo;
    }
}
