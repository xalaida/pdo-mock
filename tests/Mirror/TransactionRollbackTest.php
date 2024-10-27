<?php

namespace Tests\Xala\Elomock\Mirror;

use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Xala\Elomock\PDOMock;

class TransactionRollbackTest extends TestCase
{
    #[Test]
    #[DataProvider('connections')]
    public function itShouldRollbackTransaction(PDO $pdo): void
    {
        static::assertTrue(
            $pdo->beginTransaction()
        );

        $pdo->exec('insert into "books" ("title") values ("Kaidash’s Family")');

        static::assertTrue(
            $pdo->rollBack()
        );
    }

    public static function connections(): array
    {
        return [
            'SQLite' => [
                static::prepareSqlite()
            ],

            'Mock' => [
                static::prepareMock()
            ],
        ];
    }

    protected static function prepareSqlite(): PDO
    {
        $pdo = new PDO('sqlite::memory:');

        $pdo->exec('create table "books" ("id" integer primary key autoincrement not null, "title" varchar not null)');

        return $pdo;
    }

    protected static function prepareMock(): PDOMock
    {
        $pdo = new PDOMock();

        $pdo->expectBeginTransaction();

        $pdo->expect('insert into "books" ("title") values ("Kaidash’s Family")');

        $pdo->expectRollback();

        return $pdo;
    }
}
