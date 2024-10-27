<?php

namespace Tests\Xala\Elomock\Contract;

use PDO;
use PDOException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Xala\Elomock\PDOMock;

class TransactionNestedTest extends TestCase
{
    #[Test]
    #[DataProvider('connections')]
    public function itShouldHandleNestedTransactions(PDO $pdo): void
    {
        $pdo->setAttribute($pdo::ATTR_ERRMODE, $pdo::ERRMODE_SILENT);

        $pdo->beginTransaction();

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('There is already an active transaction');

        $pdo->beginTransaction();

        static::assertTrue($pdo->inTransaction());
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
        $pdo->expectBeginTransaction();

        return $pdo;
    }
}
