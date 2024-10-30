<?php

namespace Tests\Xala\Elomock\Contract;

use PDO;
use Tests\Xala\Elomock\TestCase;
use Xala\Elomock\PDOMock;

class LastInsertIdFreshTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     */
    public function itShouldReturnZeroAsLastInsertId(PDO $pdo)
    {
        static::assertSame('0', $pdo->lastInsertId());
        static::assertSame('0', $pdo->lastInsertId());
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
