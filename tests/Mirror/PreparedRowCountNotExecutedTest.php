<?php

namespace Tests\Xala\Elomock\Mirror;

use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Xala\Elomock\PDOMock;

class PreparedRowCountNotExecutedTest extends TestCase
{
    #[Test]
    #[DataProvider('connections')]
    public function itShouldReturnRowCountUsingNotExecutedPreparedStatement(PDO $pdo): void
    {
        $statement = $pdo->prepare('delete from "books"');

        static::assertSame(0, $statement->rowCount());
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

        $pdo->expect('delete from "books"')
            ->affecting(2);

        return $pdo;
    }
}
