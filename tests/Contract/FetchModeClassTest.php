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
        static::assertEquals(1, $row->getId());
        static::assertSame('Kaidash’s Family', $row->getTitle());

        $row = $statement->fetch($pdo::FETCH_CLASS);

        static::assertInstanceOf(BookForClassFetchMode::class, $row);
        static::assertEquals(2, $row->getId());
        static::assertSame('Shadows of the Forgotten Ancestors', $row->getTitle());
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
        static::assertEquals(1, $rows[0]->getId());
        static::assertSame('Kaidash’s Family', $rows[0]->getTitle());

        static::assertInstanceOf(BookForClassFetchMode::class, $rows[1]);
        static::assertEquals(2, $rows[1]->getId());
        static::assertSame('Shadows of the Forgotten Ancestors', $rows[1]->getTitle());
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
        static::assertEquals(1, $row->getId());
        static::assertSame('Kaidash’s Family', $row->getTitle());

        $row = $statement->fetch();

        static::assertInstanceOf(BookForClassFetchMode::class, $row);
        static::assertEquals(2, $row->getId());
        static::assertSame('Shadows of the Forgotten Ancestors', $row->getTitle());
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
        static::assertEquals(1, $row->getId());
        static::assertSame('Kaidash’s Family', $row->getTitle());
        static::assertSame(1000, $row->getPrice());
        static::assertFalse($row->getPublished());

        $row = $statement->fetch();

        static::assertInstanceOf(BookForClassFetchModeWithConstructor::class, $row);
        static::assertEquals(2, $row->getId());
        static::assertSame('Shadows of the Forgotten Ancestors', $row->getTitle());
        static::assertSame(1000, $row->getPrice());
        static::assertFalse($row->getPublished());
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
        static::assertEquals(1, $rows[0]->getId());
        static::assertSame('Kaidash’s Family', $rows[0]->getTitle());
        static::assertSame(1000, $rows[0]->getPrice());
        static::assertFalse($rows[0]->getPublished());

        static::assertInstanceOf(BookForClassFetchModeWithConstructor::class, $rows[1]);
        static::assertEquals(2, $rows[1]->getId());
        static::assertSame('Shadows of the Forgotten Ancestors', $rows[1]->getTitle());
        static::assertSame(1000, $rows[1]->getPrice());
        static::assertFalse($rows[1]->getPublished());
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldFetchAllIntoClassWithConstructorLateProps($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_STRINGIFY_FETCHES, true);

        $statement = $pdo->prepare('select * from "books"');

        $statement->execute();

        $rows = $statement->fetchAll($pdo::FETCH_CLASS | $pdo::FETCH_PROPS_LATE, BookForClassFetchModeWithConstructor::class, [1000, false]);

        static::assertCount(2, $rows);

        static::assertInstanceOf(BookForClassFetchModeWithConstructor::class, $rows[0]);
        static::assertSame('1', $rows[0]->getId());
        static::assertSame('Kaidash’s Family', $rows[0]->getTitle());
        static::assertSame('1500', $rows[0]->getPrice());
        static::assertFalse($rows[0]->getPublished());

        static::assertInstanceOf(BookForClassFetchModeWithConstructor::class, $rows[1]);
        static::assertSame('2', $rows[1]->getId());
        static::assertSame('Shadows of the Forgotten Ancestors', $rows[1]->getTitle());
        static::assertNull($rows[1]->getPrice());
        static::assertFalse($rows[1]->getPublished());
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
            ->willFetchRows([
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
    protected $id;

    /**
     * @var string|null
     */
    protected $title;

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }
}


class BookForClassFetchModeWithConstructor
{
    /**
     * @var int|string
     */
    protected $id;

    /**
     * @var string|null
     */
    protected $title;

    /**
     * @var int|null
     */
    protected $price;

    /**
     * @var bool|null
     */
    protected $published;

    /**
     * @param int|null $price
     * @param bool|null $published
     */
    public function __construct($price, $published = false)
    {
        $this->price = $price;
        $this->published = $published;
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return int|null
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return bool|null
     */
    public function getPublished()
    {
        return $this->published;
    }
}
