<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class FetchStringifyFetchesEnabledTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldReturnNumericValuesAsStrings($pdo)
    {
        $statement = $pdo->prepare('select * from "books"');

        $statement->execute();

        $pdo->setAttribute($pdo::ATTR_STRINGIFY_FETCHES, true);

        $row = $statement->fetch($pdo::FETCH_OBJ);

        static::assertSame('1', $row->id);
        static::assertSame('Kaidash’s Family', $row->title);
        static::assertSame('2024', $row->year);
        static::assertSame('9.99', $row->price);
        static::assertSame('0', $row->published);
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

        $pdo->exec('create table "books" ("id" integer primary key autoincrement not null, "title" varchar not null, "year" integer not null, "price" double not null, "published" tinyint(1) not null)');

        $pdo->exec('insert into "books" ("title", "year", "price", "published") values ("Kaidash’s Family", 2024, 9.99, 0)');

        return $pdo;
    }

    protected static function configureMock()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->andFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family', 'year' => 2024, 'price' => 9.99, 'published' => 0],
            ]);

        return $pdo;
    }
}
