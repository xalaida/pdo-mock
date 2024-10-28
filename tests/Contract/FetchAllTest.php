<?php

namespace Tests\Xala\Elomock\Contract;

use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Xala\Elomock\TestCase;
use ValueError;
use Xala\Elomock\PDOMock;

class FetchAllTest extends TestCase
{
    #[Test]
    #[DataProvider('contracts')]
    public function itShouldReturnEmptyRowsWhenStatementIsNotExecuted(PDO $pdo): void
    {
        $statement = $pdo->prepare('select * from "books"');

        $rows = $statement->fetchAll();

        static::assertSame([], $rows);
    }

    #[Test]
    #[DataProvider('contracts')]
    public function itShouldFailOnFetchAllInLazyMode(PDO $pdo): void
    {
        $statement = $pdo->prepare('select * from "books"');

        try {
            $statement->fetchAll($pdo::FETCH_LAZY);

            $this->fail('Expected exception is not thrown');
        } catch (ValueError $e) {
            static::assertSame('PDOStatement::fetchAll(): Argument #1 ($mode) cannot be PDO::FETCH_LAZY in PDOStatement::fetchAll()', $e->getMessage());
        }
    }

    #[Test]
    #[DataProvider('contracts')]
    public function itShouldHandleFetchAllInAssocMode(PDO $pdo): void
    {
        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $rows = $statement->fetchAll($pdo::FETCH_ASSOC);

        static::assertCount(2, $rows);
        static::assertIsArray($rows[0]);
        static::assertSame(['id' => 1, 'title' => 'Kaidash’s Family'], $rows[0]);
        static::assertIsArray($rows[1]);
        static::assertSame(['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'], $rows[1]);
    }

    #[Test]
    #[DataProvider('contracts')]
    public function itShouldHandleFetchAllInNumMode(PDO $pdo): void
    {
        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $rows = $statement->fetchAll($pdo::FETCH_NUM);

        static::assertCount(2, $rows);
        static::assertIsArray($rows[0]);
        static::assertSame([1, 'Kaidash’s Family'], $rows[0]);
        static::assertIsArray($rows[1]);
        static::assertSame([2, 'Shadows of the Forgotten Ancestors'], $rows[1]);
    }

    #[Test]
    #[DataProvider('contracts')]
    public function itShouldHandleFetchAllInBothMode(PDO $pdo): void
    {
        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $rows = $statement->fetchAll($pdo::FETCH_BOTH);

        static::assertCount(2, $rows);
        static::assertIsArray($rows[0]);
        static::assertEquals([0 => 1, 'id' => 1, 1 => 'Kaidash’s Family', 'title' => 'Kaidash’s Family'], $rows[0]);
        static::assertIsArray($rows[1]);
        static::assertEquals([0 => 2, 'id' => 2, 1 => 'Shadows of the Forgotten Ancestors', 'title' => 'Shadows of the Forgotten Ancestors'], $rows[1]);
    }

    #[Test]
    #[DataProvider('contracts')]
    public function itShouldHandleFetchAllInObjMode(PDO $pdo): void
    {
        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $rows = $statement->fetchAll($pdo::FETCH_OBJ);

        static::assertCount(2, $rows);
        static::assertIsObject($rows[0]);
        static::assertEquals((object) ['id' => 1, 'title' => 'Kaidash’s Family'], $rows[0]);
        static::assertIsObject($rows[1]);
        static::assertEquals((object) ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'], $rows[1]);
    }

    #[Test]
    #[DataProvider('contracts')]
    public function itShouldHandleFetchAllInBothModeAsDefault(PDO $pdo): void
    {
        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $rows = $statement->fetchAll();

        static::assertCount(2, $rows);
        static::assertIsArray($rows[0]);
        static::assertEquals([0 => 1, 'id' => 1, 1 => 'Kaidash’s Family', 'title' => 'Kaidash’s Family'], $rows[0]);
        static::assertIsArray($rows[1]);
        static::assertEquals([0 => 2, 'id' => 2, 1 => 'Shadows of the Forgotten Ancestors', 'title' => 'Shadows of the Forgotten Ancestors'], $rows[1]);
    }

    #[Test]
    #[DataProvider('contracts')]
    public function itShouldUseCustomDefaultFetchMode(PDO $pdo): void
    {
        $pdo->setAttribute($pdo::ATTR_DEFAULT_FETCH_MODE, $pdo::FETCH_OBJ);

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $rows = $statement->fetchAll();

        static::assertCount(2, $rows);
        static::assertIsObject($rows[0]);
        static::assertEquals((object) ['id' => 1, 'title' => 'Kaidash’s Family'], $rows[0]);
        static::assertIsObject($rows[1]);
        static::assertEquals((object) ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'], $rows[1]);
    }

    #[Test]
    #[DataProvider('contracts')]
    public function itShouldUseCustomDefaultFetchModeForStatement(PDO $pdo): void
    {
        $statement = $pdo->prepare('select * from "books"');

        $statement->setFetchMode($pdo::FETCH_OBJ);

        $result = $statement->execute();

        static::assertTrue($result);

        $rows = $statement->fetchAll();

        static::assertCount(2, $rows);
        static::assertIsObject($rows[0]);
        static::assertEquals((object) ['id' => 1, 'title' => 'Kaidash’s Family'], $rows[0]);
        static::assertIsObject($rows[1]);
        static::assertEquals((object) ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'], $rows[1]);
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
