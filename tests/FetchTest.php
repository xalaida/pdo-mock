<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;
use Xala\Elomock\PDOMock;

class FetchTest extends TestCase
{
    #[Test]
    public function itShouldHandleFetch(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared()
            ->andFetchRecords([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

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
    public function itShouldReturnFalseWhenStatementIsNotExecuted(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"');

        $statement = $pdo->prepare('select * from "books"');

        $row = $statement->fetch($pdo::FETCH_ASSOC);

        static::assertFalse($row);
    }

    #[Test]
    public function itShouldHandleFetchInAssocMode(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared()
            ->andFetchRecords([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

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
    public function itShouldHandleFetchInNumMode(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared()
            ->andFetchRecords([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

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
    public function itShouldHandleFetchInBothMode(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared()
            ->andFetchRecords([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

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
    public function itShouldHandleFetchInObjMode(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared()
            ->andFetchRecords([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

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
}
