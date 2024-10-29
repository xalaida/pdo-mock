<?php

namespace Tests\Xala\Elomock\Contract;

use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Xala\Elomock\TestCase;
use ValueError;
use Xala\Elomock\PDOMock;

class FetchModeBoundInvalidColumnIndexTest extends TestCase
{
    #[Test]
    #[DataProvider('contracts')]
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

    public static function contracts(): array
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

        $pdo->exec('create table "books" (
            "id" integer primary key autoincrement not null, 
            "title" varchar not null
        )');

        $pdo->exec('insert into "books" ("title") values ("Kaidash’s Family")');

        return $pdo;
    }

    protected static function configureMock(): PDOMock
    {
        $pdo = new PDOMock();

        $pdo->expect('select "title" from "books"')
            ->andFetchRows([
                ['title' => 'Kaidash’s Family'],
            ]);

        return $pdo;
    }
}
