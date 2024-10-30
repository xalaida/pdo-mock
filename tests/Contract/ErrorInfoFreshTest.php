<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class ErrorInfoFreshTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     */
    public function itShouldDisplayErrorInformationForPDOInstance($pdo)
    {
        static::assertNull($pdo->errorCode());
        static::assertSame(['', null, null], $pdo->errorInfo());
    }

    public static function contracts()
    {
        return [
            'SQLite' => [
                static::configureSqlite(),
            ],

            'Mock' => [
                static::configureMock(),
            ],
        ];
    }

    protected static function configureSqlite()
    {
        return new PDO('sqlite::memory:');
    }

    protected static function configureMock()
    {
        return new PDOMock();
    }
}
