<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class TransactionCommitTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     */
    public function itShouldCommitTransaction($pdo)
    {
        static::assertFalse(
            $pdo->inTransaction()
        );

        static::assertTrue(
            $pdo->beginTransaction()
        );

        $pdo->exec('insert into "books" ("title") values ("Kaidash’s Family")');

        static::assertTrue(
            $pdo->inTransaction()
        );

        static::assertTrue(
            $pdo->commit()
        );

        static::assertFalse(
            $pdo->inTransaction()
        );
    }

    public static function contracts()
    {
        return [
            'SQLite' => [
                static::configureSqlite()
            ],

            'Mock' => [
                static::configureMock()
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

        $pdo->expectCommit();

        return $pdo;
    }
}
