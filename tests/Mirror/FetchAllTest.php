<?php

namespace Tests\Xala\Elomock\Mirror;

use PDO;
use PHPUnit\Framework\Attributes\Test;
use ValueError;
use Xala\Elomock\PDOMock;

class FetchAllTest extends TestCase
{
    #[Test]
    public function itShouldReturnEmptyRowsWhenStatementIsNotExecuted(): void
    {
        $scenario = function (PDO $pdo) {
            $statement = $pdo->prepare('select * from "books"');

            $rows = $statement->fetchAll();

            static::assertSame([], $rows);
        };

        $scenario($this->prepareSqlite());
        $scenario($this->prepareMock());
    }

    #[Test]
    public function itShouldFailOnFetchAllInLazyMode(): void
    {
        $scenario = function (PDO $pdo) {
            $statement = $pdo->prepare('select * from "books"');

            try {
                $statement->fetchAll($pdo::FETCH_LAZY);

                $this->fail('Expected exception is not thrown');
            } catch (ValueError $e) {
                static::assertSame('PDOStatement::fetchAll(): Argument #1 ($mode) cannot be PDO::FETCH_LAZY in PDOStatement::fetchAll()', $e->getMessage());
            }
        };

        $scenario($this->prepareSqlite());
        $scenario($this->prepareMock());
    }

    #[Test]
    public function itShouldHandleFetchAllInAssocMode(): void
    {
        $scenario = function (PDO $pdo) {
            $statement = $pdo->prepare('select * from "books"');

            $result = $statement->execute();

            static::assertTrue($result);

            $rows = $statement->fetchAll($pdo::FETCH_ASSOC);

            static::assertCount(2, $rows);
            static::assertIsArray($rows[0]);
            static::assertSame(['id' => 1, 'title' => 'Kaidash’s Family'], $rows[0]);
            static::assertIsArray($rows[1]);
            static::assertSame(['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'], $rows[1]);
        };

        $scenario($this->prepareSqlite());
        $scenario($this->prepareMock());
    }

    #[Test]
    public function itShouldHandleFetchAllInNumMode(): void
    {
        $scenario = function (PDO $pdo) {
            $statement = $pdo->prepare('select * from "books"');

            $result = $statement->execute();

            static::assertTrue($result);

            $rows = $statement->fetchAll($pdo::FETCH_NUM);

            static::assertCount(2, $rows);
            static::assertIsArray($rows[0]);
            static::assertSame([1, 'Kaidash’s Family'], $rows[0]);
            static::assertIsArray($rows[1]);
            static::assertSame([2, 'Shadows of the Forgotten Ancestors'], $rows[1]);
        };

        $scenario($this->prepareSqlite());
        $scenario($this->prepareMock());
    }

    #[Test]
    public function itShouldHandleFetchAllInBothMode(): void
    {
        $scenario = function (PDO $pdo) {
            $statement = $pdo->prepare('select * from "books"');

            $result = $statement->execute();

            static::assertTrue($result);

            $rows = $statement->fetchAll($pdo::FETCH_BOTH);

            static::assertCount(2, $rows);
            static::assertIsArray($rows[0]);
            static::assertEquals([0 => 1, 'id' => 1, 1 => 'Kaidash’s Family', 'title' => 'Kaidash’s Family'], $rows[0]);
            static::assertIsArray($rows[1]);
            static::assertEquals([0 => 2, 'id' => 2, 1 => 'Shadows of the Forgotten Ancestors', 'title' => 'Shadows of the Forgotten Ancestors'], $rows[1]);
        };

        $scenario($this->prepareSqlite());
        $scenario($this->prepareMock());
    }

    #[Test]
    public function itShouldHandleFetchAllInObjMode(): void
    {
        $scenario = function (PDO $pdo) {
            $statement = $pdo->prepare('select * from "books"');

            $result = $statement->execute();

            static::assertTrue($result);

            $rows = $statement->fetchAll($pdo::FETCH_OBJ);

            static::assertCount(2, $rows);
            static::assertIsObject($rows[0]);
            static::assertEquals((object) ['id' => 1, 'title' => 'Kaidash’s Family'], $rows[0]);
            static::assertIsObject($rows[1]);
            static::assertEquals((object) ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'], $rows[1]);
        };

        $scenario($this->prepareSqlite());
        $scenario($this->prepareMock());
    }

    #[Test]
    public function itShouldHandleFetchAllInBothModeAsDefault(): void
    {
        $scenario = function (PDO $pdo) {
            $statement = $pdo->prepare('select * from "books"');

            $result = $statement->execute();

            static::assertTrue($result);

            $rows = $statement->fetchAll();

            static::assertCount(2, $rows);
            static::assertIsArray($rows[0]);
            static::assertEquals([0 => 1, 'id' => 1, 1 => 'Kaidash’s Family', 'title' => 'Kaidash’s Family'], $rows[0]);
            static::assertIsArray($rows[1]);
            static::assertEquals([0 => 2, 'id' => 2, 1 => 'Shadows of the Forgotten Ancestors', 'title' => 'Shadows of the Forgotten Ancestors'], $rows[1]);
        };

        $scenario($this->prepareSqlite());
        $scenario($this->prepareMock());
    }

    #[Test]
    public function itShouldUseCustomDefaultFetchMode(): void
    {
        $scenario = function (PDO $pdo) {
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
        };

        $scenario($this->prepareSqlite());
        $scenario($this->prepareMock());
    }

    #[Test]
    public function itShouldUseCustomDefaultFetchModeForStatement(): void
    {
        $scenario = function (PDO $pdo) {
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
        };

        $scenario($this->prepareSqlite());
        $scenario($this->prepareMock());
    }

    protected function prepareSqlite(): PDO
    {
        $sqlite = $this->sqlite();

        $sqlite->exec('insert into "books" ("title") values ("Kaidash’s Family"), ("Shadows of the Forgotten Ancestors")');

        return $sqlite;
    }

    protected function prepareMock(): PDOMock
    {
        $mock = new PDOMock();

        $mock->expect('select * from "books"')
            ->andFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        return $mock;
    }
}
