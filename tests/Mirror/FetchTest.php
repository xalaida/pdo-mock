<?php

namespace Tests\Xala\Elomock\Mirror;

use PDO;
use PHPUnit\Framework\Attributes\Test;
use Xala\Elomock\PDOMock;

/**
 * @todo handle different fetch modes in cursor mode
 * @todo handle other fetch modes
 */
class FetchTest extends TestCase
{
    #[Test]
    public function itShouldHandleFetch(): void
    {
        $scenario = function (PDO $pdo) {
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
        };

        $scenario($this->prepareSqlite());
        $scenario($this->prepareMock());
    }

    #[Test]
    public function itShouldReturnFalseWhenStatementIsNotExecuted(): void
    {
        $scenario = function (PDO $pdo) {
            $statement = $pdo->prepare('select * from "books"');

            $row = $statement->fetch($pdo::FETCH_ASSOC);

            static::assertFalse($row);
        };

        $scenario($this->prepareSqlite());
        $scenario($this->prepareMock());
    }

    #[Test]
    public function itShouldHandleFetchInAssocMode(): void
    {
        $scenario = function (PDO $pdo) {
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
        };

        $scenario($this->prepareSqlite());
        $scenario($this->prepareMock());
    }

    #[Test]
    public function itShouldHandleFetchInNumMode(): void
    {
        $scenario = function (PDO $pdo) {
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
        };

        $scenario($this->prepareSqlite());
        $scenario($this->prepareMock());
    }

    #[Test]
    public function itShouldHandleFetchInBothMode(): void
    {
        $scenario = function (PDO $pdo) {
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
        };

        $scenario($this->prepareSqlite());
        $scenario($this->prepareMock());
    }

    #[Test]
    public function itShouldHandleFetchInObjMode(): void
    {
        $scenario = function (PDO $pdo) {
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
