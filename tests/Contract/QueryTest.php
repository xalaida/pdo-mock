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
     * @return void
     */
    public function itShouldQueryUsingDefaultFetchMode($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_STRINGIFY_FETCHES, true);

        $statement = $pdo->query('select * from "books"');

        $rows = $statement->fetchAll();

        static::assertCount(2, $rows);
        static::assertIsArrayType($rows[0]);
        static::assertSame('1', $rows[0][0]);
        static::assertSame('1', $rows[0]['id']);
        static::assertSame('Kaidash’s Family', $rows[0][1]);
        static::assertSame('Kaidash’s Family', $rows[0]['title']);
        static::assertIsArrayType($rows[1]);
        static::assertSame('2', $rows[1]['id']);
        static::assertSame('2', $rows[1][0]);
        static::assertSame('Shadows of the Forgotten Ancestors', $rows[1][1]);
        static::assertSame('Shadows of the Forgotten Ancestors', $rows[1]['title']);
        $this->assertInstanceOf(PDOStatement::class, $statement);
        static::assertSame(0, $statement->rowCount());
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
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
     * @return void
     */
    public function itShouldFetchIntoClassUsingQuery($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_STRINGIFY_FETCHES, true);

        // @phpstan-ignore-next-line
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

    /**
     * @return array<string, array<int, PDO>>
     */
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

    /**
     * @return PDO
     */
    protected static function configureSqlite()
    {
        $pdo = new PDO('sqlite::memory:');

        $pdo->exec('create table "books" ("id" integer primary key autoincrement not null, "title" varchar not null)');

        $pdo->exec('insert into "books" ("title") values ("Kaidash’s Family"), ("Shadows of the Forgotten Ancestors")');

        return $pdo;
    }

    /**
     * @return PDOMock
     */
    protected static function configureMock()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->willFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        return $pdo;
    }
}

class BookForQuery
{
    /**
     * @var int|string
     */
    public $id;

    /**
     * @var string|null
     */
    public $title;
}
