<?php

namespace Tests\Xala\Elomock\Contract;

use PDO;
use Tests\Xala\Elomock\TestCase;
use Xala\Elomock\PDOMock;

class LastInsertIdTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     */
    public function itShouldUseLastInsertIdFromQuery(PDO $pdo)
    {
        $pdo->exec('insert into "books" ("id", "title") values (777, "Kaidash’s Family")');

        static::assertSame('777', $pdo->lastInsertId());
        static::assertSame('777', $pdo->lastInsertId());
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
        $pdo = new PDO('sqlite::memory:');

        $pdo->exec('create table "books" ("id" integer primary key autoincrement not null, "title" varchar not null)');

        return $pdo;
    }

    protected static function configureMock()
    {
        $pdo = new PDOMock();

        $pdo->expect('insert into "books" ("id", "title") values (777, "Kaidash’s Family")')
            ->withInsertId(777);

        return $pdo;
    }
}
