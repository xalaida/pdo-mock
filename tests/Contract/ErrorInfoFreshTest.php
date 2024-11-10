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
     * @param PDO $pdo
     * @return void
     */
    public function itShouldDisplayErrorInformationForPDOInstance($pdo)
    {
        static::assertNull($pdo->errorCode());
        static::assertSame(['', null, null], $pdo->errorInfo());
    }

    /**
     * @return array<string, array<int, PDO>>
     */
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

    /**
     * @return PDO
     */
    protected static function configureSqlite()
    {
        return new PDO('sqlite::memory:');
    }

    /**
     * @return PDOMock
     */
    protected static function configureMock()
    {
        return new PDOMock();
    }
}
