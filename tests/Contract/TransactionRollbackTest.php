<?php

namespace Tests\Xala\Elomock\Contract;

use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Xala\Elomock\TestCase;
use Xala\Elomock\PDOMock;

class TransactionRollbackTest extends TestCase
{
    #[Test]
    #[DataProvider('contracts')]
    public function itShouldRollbackTransaction(PDO $pdo): void
    {
        static::assertTrue(
            $pdo->beginTransaction(),
        );

        $pdo->exec('insert into "books" ("title") values ("Kaidash’s Family")');

        static::assertTrue(
            $pdo->rollBack(),
        );
    }

    public static function contracts(): array
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

        $pdo->expectRollback();

        return $pdo;
    }
}
