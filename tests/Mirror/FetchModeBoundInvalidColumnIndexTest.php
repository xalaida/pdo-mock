<?php

namespace Tests\Xala\Elomock\Mirror;

use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use ValueError;
use Xala\Elomock\PDOMock;

class FetchModeBoundInvalidColumnIndexTest extends TestCase
{
    #[Test]
    #[DataProvider('connections')]
    public function itShouldThrowValueExceptionWhenInvalidColumnIndex(PDO $pdo): void
    {
        $pdo->setAttribute($pdo::ATTR_ERRMODE, $pdo::ERRMODE_SILENT);

        $statement = $pdo->prepare('select "title" from "books"');

        $statement->setFetchMode($pdo::FETCH_BOUND);

        $statement->bindColumn(1, $title);
        $statement->bindColumn(2, $status);

        $result = $statement->execute();

        static::assertTrue($result);

        try {
            $statement->fetch();

            $this->fail('Expected exception was not thrown');
        } catch (ValueError $e) {
            static::assertSame('Kaidash’s Family', $title);
            static::assertSame('Invalid column index', $e->getMessage());
        }
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
        $sqlite = new PDO('sqlite::memory:');

        $sqlite->exec('create table "books" (
            "id" integer primary key autoincrement not null, 
            "title" varchar not null
        )');

        $sqlite->exec('insert into "books" ("title") values ("Kaidash’s Family")');

        return $sqlite;
    }

    protected static function prepareMock(): PDOMock
    {
        $mock = new PDOMock();

        $mock->expect('select "title" from "books"')
            ->andFetchRows([
                ['title' => 'Kaidash’s Family'],
            ]);

        return $mock;
    }
}
