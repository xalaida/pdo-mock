<?php

namespace Xalaida\PDOMock\Adapters\PHPUnit;

use PHPUnit\Framework\TestCase;
use Xalaida\PDOMock\PDOMock;

class PDOMockFactory
{
    /**
     * @param TestCase $phpunit
     * @return PDOMock
     */
    public static function forTestCase($phpunit)
    {
        $pdo = new PDOMock();

        $pdo->expectationValidator->useCallback(function () use ($phpunit) {
            $phpunit->addToAssertionCount(1);
        });

        return $pdo;
    }
}
