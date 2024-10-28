<?php

namespace Tests\Xala\Elomock\Contract;

use Iterator;
use IteratorAggregate;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Xala\Elomock\TestCase;
use Xala\Elomock\PDOMock;

class FetchTest extends TestCase
{
    #[Test]
    #[DataProvider('contracts')]
    public function itShouldHandleFetch(PDO $pdo): void
    {
        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $row = $statement->fetch();

        static::assertIsArray($row);
        static::assertEquals([0 => 1, 'id' => 1, 1 => 'Kaidash’s Family', 'title' => 'Kaidash’s Family'], $row);

        $row = $statement->fetch();

        static::assertIsArray($row);
        static::assertEquals([0 => 2, 'id' => 2, 1 => 'Shadows of the Forgotten Ancestors', 'title' => 'Shadows of the Forgotten Ancestors'], $row);

        $row = $statement->fetch();

        static::assertFalse($row);
    }

    #[Test]
    #[DataProvider('contracts')]
    public function itShouldReturnFalseWhenStatementIsNotExecuted(PDO $pdo): void
    {
        $statement = $pdo->prepare('select * from "books"');

        $row = $statement->fetch($pdo::FETCH_ASSOC);

        static::assertFalse($row);
    }

    #[Test]
    #[DataProvider('contracts')]
    public function itShouldHandleFetchInAssocMode(PDO $pdo): void
    {
        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $row = $statement->fetch($pdo::FETCH_ASSOC);

        static::assertIsArray($row);
        static::assertSame(['id' => 1, 'title' => 'Kaidash’s Family'], $row);

        $row = $statement->fetch($pdo::FETCH_ASSOC);

        static::assertIsArray($row);
        static::assertSame(['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'], $row);

        $row = $statement->fetch($pdo::FETCH_ASSOC);

        static::assertFalse($row);
    }

    #[Test]
    #[DataProvider('contracts')]
    public function itShouldHandleFetchInNumMode(PDO $pdo): void
    {
        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $row = $statement->fetch($pdo::FETCH_NUM);

        static::assertIsArray($row);
        static::assertSame([1, 'Kaidash’s Family'], $row);

        $row = $statement->fetch($pdo::FETCH_NUM);

        static::assertIsArray($row);
        static::assertSame([2,'Shadows of the Forgotten Ancestors'], $row);

        $row = $statement->fetch($pdo::FETCH_NUM);

        static::assertFalse($row);
    }

    #[Test]
    #[DataProvider('contracts')]
    public function itShouldHandleFetchInBothMode(PDO $pdo): void
    {
        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $row = $statement->fetch($pdo::FETCH_BOTH);

        static::assertIsArray($row);
        static::assertEquals([0 => 1, 'id' => 1, 1 => 'Kaidash’s Family', 'title' => 'Kaidash’s Family'], $row);

        $row = $statement->fetch($pdo::FETCH_BOTH);

        static::assertIsArray($row);
        static::assertEquals([0 => 2, 'id' => 2, 1 => 'Shadows of the Forgotten Ancestors', 'title' => 'Shadows of the Forgotten Ancestors'], $row);

        $row = $statement->fetch($pdo::FETCH_BOTH);

        static::assertFalse($row);
    }

    #[Test]
    #[DataProvider('contracts')]
    public function itShouldHandleFetchInObjMode(PDO $pdo): void
    {
        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $row = $statement->fetch($pdo::FETCH_OBJ);

        static::assertIsObject($row);
        static::assertEquals((object) ['id' => 1, 'title' => 'Kaidash’s Family'], $row);

        $row = $statement->fetch($pdo::FETCH_OBJ);

        static::assertIsObject($row);
        static::assertEquals((object) ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'], $row);

        $row = $statement->fetch($pdo::FETCH_OBJ);

        static::assertFalse($row);
    }

    #[Test]
    #[DataProvider('contracts')]
    public function itShouldUseFetchAsIterator(PDO $pdo): void
    {
        $statement = $pdo->prepare('select * from "books"');

        $statement->setFetchMode($pdo::FETCH_OBJ);

        $result = $statement->execute();

        static::assertTrue($result);

        static::assertInstanceOf(IteratorAggregate::class, $statement);

        $iterator = $statement->getIterator();

        static::assertInstanceOf(Iterator::class, $iterator);

        static::assertEquals(0, $iterator->key());
        static::assertEquals((object) ['id' => 1, 'title' => 'Kaidash’s Family'], $iterator->current());

        $iterator->next();

        static::assertEquals(1, $iterator->key());
        static::assertEquals((object) ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'], $iterator->current());

        $iterator->next();

        static::assertNull($iterator->current());
        static::assertFalse($iterator->valid());
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

        $pdo->exec('create table "books" ("id" integer primary key autoincrement not null, "title" varchar not null)');

        $pdo->exec('insert into "books" ("title") values ("Kaidash’s Family"), ("Shadows of the Forgotten Ancestors")');

        return $pdo;
    }

    protected static function configureMock(): PDOMock
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->andFetchRecords([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        return $pdo;
    }
}
