<?php

namespace Tests\Xala\Elomock\Contract;

use PDO;
use Tests\Xala\Elomock\TestCase;
use Xala\Elomock\PDOMock;

class ErrorInfoFreshTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     */
    public function itShouldDisplayErrorInformationForPDOInstance(PDO $pdo)
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
