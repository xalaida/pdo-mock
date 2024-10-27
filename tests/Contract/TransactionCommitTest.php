<?php

namespace Tests\Xala\Elomock\Contract;

use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Xala\Elomock\PDOMock;

class TransactionCommitTest extends TestCase
{
    #[Test]
    #[DataProvider('connections')]
    public function itShouldCommitTransaction(PDO $pdo): void
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

    public static function connections(): array
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

    protected static function configureSqlite(): PDO
    {
        $pdo = new PDO('sqlite::memory:');

        $pdo->exec('create table "books" ("id" integer primary key autoincrement not null, "title" varchar not null)');

        return $pdo;
    }

    protected static function configureMock(): PDOMock
    {
        $pdo = new PDOMock();

        $pdo->expectBeginTransaction();

        $pdo->expect('insert into "books" ("title") values ("Kaidash’s Family")');

        $pdo->expectCommit();

        return $pdo;
    }
}
