<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class FetchModeClassTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldFetchIntoClass($pdo)
    {
        $statement = $pdo->query('select * from "books"');

        $statement->setFetchMode($pdo::FETCH_CLASS, BookForClassFetchMode::class);

        $row = $statement->fetch($pdo::FETCH_CLASS);

        static::assertInstanceOf(BookForClassFetchMode::class, $row);
        static::assertEquals(1, $row->id);
        static::assertSame('Kaidash’s Family', $row->title);

        $row = $statement->fetch($pdo::FETCH_CLASS);

        static::assertInstanceOf(BookForClassFetchMode::class, $row);
        static::assertEquals(2, $row->id);
        static::assertSame('Shadows of the Forgotten Ancestors', $row->title);
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldFetchAllIntoClass($pdo)
    {
        $statement = $pdo->prepare('select * from "books"');

        $statement->execute();

        $rows = $statement->fetchAll($pdo::FETCH_CLASS, BookForClassFetchMode::class);

        static::assertCount(2, $rows);

        static::assertInstanceOf(BookForClassFetchMode::class, $rows[0]);
        static::assertEquals(1, $rows[0]->id);
        static::assertSame('Kaidash’s Family', $rows[0]->title);

        static::assertInstanceOf(BookForClassFetchMode::class, $rows[1]);
        static::assertEquals(2, $rows[1]->id);
        static::assertSame('Shadows of the Forgotten Ancestors', $rows[1]->title);
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldFetchIntoClassUsingDefaultFetchMode($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_STRINGIFY_FETCHES, false);

        $statement = $pdo->query('select * from "books"');

        $statement->setFetchMode($pdo::FETCH_CLASS, BookForClassFetchMode::class);

        $row = $statement->fetch();

        static::assertInstanceOf(BookForClassFetchMode::class, $row);
        static::assertEquals(1, $row->id);
        static::assertSame('Kaidash’s Family', $row->title);

        $row = $statement->fetch();

        static::assertInstanceOf(BookForClassFetchMode::class, $row);
        static::assertEquals(2, $row->id);
        static::assertSame('Shadows of the Forgotten Ancestors', $row->title);
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldFetchIntoClassWithConstructor($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_STRINGIFY_FETCHES, false);

        $statement = $pdo->query('select * from "books"');

        $statement->setFetchMode($pdo::FETCH_CLASS, BookForClassFetchModeWithConstructor::class, [1000, false]);

        $row = $statement->fetch();

        static::assertInstanceOf(BookForClassFetchModeWithConstructor::class, $row);
        static::assertEquals(1, $row->id);
        static::assertSame('Kaidash’s Family', $row->title);
        static::assertSame(1000, $row->price);
        static::assertFalse($row->published);

        $row = $statement->fetch();

        static::assertInstanceOf(BookForClassFetchModeWithConstructor::class, $row);
        static::assertEquals(2, $row->id);
        static::assertSame('Shadows of the Forgotten Ancestors', $row->title);
        static::assertSame(1000, $row->price);
        static::assertFalse($row->published);
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldFetchAllIntoClassWithConstructor($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_STRINGIFY_FETCHES, false);

        $statement = $pdo->prepare('select * from "books"');

        $statement->execute();

        $rows = $statement->fetchAll($pdo::FETCH_CLASS, BookForClassFetchModeWithConstructor::class, [1000, false]);

        static::assertCount(2, $rows);

        static::assertInstanceOf(BookForClassFetchModeWithConstructor::class, $rows[0]);
        static::assertEquals(1, $rows[0]->id);
        static::assertSame('Kaidash’s Family', $rows[0]->title);
        static::assertSame(1000, $rows[0]->price);
        static::assertFalse($rows[0]->published);

        static::assertInstanceOf(BookForClassFetchModeWithConstructor::class, $rows[1]);
        static::assertEquals(2, $rows[1]->id);
        static::assertSame('Shadows of the Forgotten Ancestors', $rows[1]->title);
        static::assertSame(1000, $rows[1]->price);
        static::assertFalse($rows[1]->published);
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

        $pdo->exec('create table "books" ("id" integer primary key autoincrement not null, "title" varchar not null, "price" integer)');

        $pdo->exec('insert into "books" ("title", "price") values ("Kaidash’s Family", 1500), ("Shadows of the Forgotten Ancestors", null)');

        return $pdo;
    }

    /**
     * @return PDOMock
     */
    protected static function configureMock()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->andFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family', 'price' => 1500],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors', 'price' => null],
            ]);

        return $pdo;
    }
}

class BookForClassFetchMode
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


class BookForClassFetchModeWithConstructor
{
    /**
     * @var int|string
     */
    public $id;

    /**
     * @var string|null
     */
    public $title;

    /**
     * @var int|null
     */
    public $price;

    /**
     * @var bool|null
     */
    public $published;

    /**
     * @param int|null $price
     * @param bool|null $published
     */
    public function __construct($price, $published = false)
    {
        $this->price = $price;
        $this->published = $published;
    }
}
