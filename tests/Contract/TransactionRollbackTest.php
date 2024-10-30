<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class TransactionRollbackTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     */
    public function itShouldRollbackTransaction($pdo)
    {
        static::assertTrue(
            $pdo->beginTransaction()
        );

        $pdo->exec('insert into "books" ("title") values ("Kaidash’s Family")');

        static::assertTrue(
            $pdo->rollBack()
        );
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

        $pdo->expectBeginTransaction();

        $pdo->expect('insert into "books" ("title") values ("Kaidash’s Family")');

        $pdo->expectRollback();

        return $pdo;
    }
}
