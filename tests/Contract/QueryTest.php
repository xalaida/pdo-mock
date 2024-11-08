<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use PDOStatement;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class QueryTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldFetchAsObjects($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_STRINGIFY_FETCHES, true);

        $statement = $pdo->query('select * from "books"', $pdo::FETCH_OBJ);

        $rows = $statement->fetchAll();

        static::assertCount(2, $rows);
        static::assertIsObjectType($rows[0]);
        static::assertSame('1', $rows[0]->id);
        static::assertSame('Kaidash’s Family', $rows[0]->title);
        static::assertIsObjectType($rows[1]);
        static::assertSame('2', $rows[1]->id);
        static::assertSame('Shadows of the Forgotten Ancestors', $rows[1]->title);
        $this->assertInstanceOf(PDOStatement::class, $statement);
        static::assertSame(0, $statement->rowCount());
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldFetchIntoClassUsingQuery($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_STRINGIFY_FETCHES, true);

        $statement = $pdo->query('select * from "books"', $pdo::FETCH_CLASS, BookForQuery::class);

        $row = $statement->fetch();

        static::assertInstanceOf(BookForQuery::class, $row);
        static::assertSame('1', $row->id);
        static::assertSame('Kaidash’s Family', $row->title);

        $row = $statement->fetch();

        static::assertInstanceOf(BookForQuery::class, $row);
        static::assertSame('2', $row->id);
        static::assertSame('Shadows of the Forgotten Ancestors', $row->title);
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

        $pdo->exec('create table "books" ("id" integer primary key autoincrement not null, "title" varchar not null)');

        $pdo->exec('insert into "books" ("title") values ("Kaidash’s Family"), ("Shadows of the Forgotten Ancestors")');

        return $pdo;
    }

    protected static function configureMock()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->andFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        return $pdo;
    }
}

class BookForQuery
{
    public $id;

    public $title;
}
