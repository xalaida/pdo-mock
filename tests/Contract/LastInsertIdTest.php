<?php

namespace Tests\Xala\Elomock\Contract;

use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Xala\Elomock\TestCase;
use Xala\Elomock\PDOMock;

class LastInsertIdTest extends TestCase
{
    #[Test]
    #[DataProvider('contracts')]
    public function itShouldUseLastInsertIdFromQuery(PDO $pdo): void
    {
        $pdo->exec('insert into "books" ("id", "title") values (777, "Kaidash’s Family")');

        static::assertSame('777', $pdo->lastInsertId());
        static::assertSame('777', $pdo->lastInsertId());
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

        $pdo->expect('insert into "books" ("id", "title") values (777, "Kaidash’s Family")')
            ->withInsertId(777);

        return $pdo;
    }
}
