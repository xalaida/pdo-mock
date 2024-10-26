<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Xala\Elomock\PDOMock;

/**
 * @todo handle rewriting default fetch mode
 * @todo handle different fetch modes in cursor mode
 * @todo handle other fetch modes
 * @todo ensure query is executed before fetching
 * @todo add ability to fetch from different sources (csv file, generator, from class objects, etc)
 */
class FetchTest extends TestCase
{
    #[Test]
    public function itShouldHandleFetch(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "users"')
            ->toBePrepared()
            ->andFetchRows([
                ['id' => 1, 'name' => 'john'],
                ['id' => 2, 'name' => 'jane'],
            ]);

        $statement = $pdo->prepare('select * from "users"');

        $result = $statement->execute();

        static::assertTrue($result);

        $row = $statement->fetch();

        static::assertIsArray($row);
        static::assertEquals([0 => 1, 'id' => 1, 1 => 'john', 'name' => 'john'], $row);

        $row = $statement->fetch();

        static::assertIsArray($row);
        static::assertEquals([0 => 2, 'id' => 2, 1 => 'jane', 'name' => 'jane'], $row);

        $row = $statement->fetch();

        static::assertFalse($row);
    }

    #[Test]
    public function itShouldReturnFalseWhenStatementIsNotExecuted(): void
    {
        $pdo = new PDOMock();

        $statement = $pdo->prepare('select * from "users"');

        $row = $statement->fetch($pdo::FETCH_ASSOC);

        static::assertFalse($row);
    }

    #[Test]
    public function itShouldHandleFetchInAssocMode(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "users"')
            ->toBePrepared()
            ->andFetchRows([
                ['id' => 1, 'name' => 'john'],
                ['id' => 2, 'name' => 'jane'],
            ]);

        $statement = $pdo->prepare('select * from "users"');

        $result = $statement->execute();

        static::assertTrue($result);

        $row = $statement->fetch($pdo::FETCH_ASSOC);

        static::assertIsArray($row);
        static::assertSame(['id' => 1, 'name' => 'john'], $row);

        $row = $statement->fetch($pdo::FETCH_ASSOC);

        static::assertIsArray($row);
        static::assertSame(['id' => 2, 'name' => 'jane'], $row);

        $row = $statement->fetch($pdo::FETCH_ASSOC);

        static::assertFalse($row);
    }

    #[Test]
    public function itShouldHandleFetchInNumMode(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "users"')
            ->toBePrepared()
            ->andFetchRows([
                ['id' => 1, 'name' => 'john'],
                ['id' => 2, 'name' => 'jane'],
            ]);

        $statement = $pdo->prepare('select * from "users"');

        $result = $statement->execute();

        static::assertTrue($result);

        $row = $statement->fetch($pdo::FETCH_NUM);

        static::assertIsArray($row);
        static::assertSame([1, 'john'], $row);

        $row = $statement->fetch($pdo::FETCH_NUM);

        static::assertIsArray($row);
        static::assertSame([2,'jane'], $row);

        $row = $statement->fetch($pdo::FETCH_NUM);

        static::assertFalse($row);
    }

    #[Test]
    public function itShouldHandleFetchInBothMode(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "users"')
            ->toBePrepared()
            ->andFetchRows([
                ['id' => 1, 'name' => 'john'],
                ['id' => 2, 'name' => 'jane'],
            ]);

        $statement = $pdo->prepare('select * from "users"');

        $result = $statement->execute();

        static::assertTrue($result);

        $row = $statement->fetch($pdo::FETCH_BOTH);

        static::assertIsArray($row);
        static::assertEquals([0 => 1, 'id' => 1, 1 => 'john', 'name' => 'john'], $row);

        $row = $statement->fetch($pdo::FETCH_BOTH);

        static::assertIsArray($row);
        static::assertEquals([0 => 2, 'id' => 2, 1 => 'jane', 'name' => 'jane'], $row);

        $row = $statement->fetch($pdo::FETCH_BOTH);

        static::assertFalse($row);
    }

    #[Test]
    public function itShouldHandleFetchInObjMode(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "users"')
            ->toBePrepared()
            ->andFetchRows([
                ['id' => 1, 'name' => 'john'],
                ['id' => 2, 'name' => 'jane'],
            ]);

        $statement = $pdo->prepare('select * from "users"');

        $result = $statement->execute();

        static::assertTrue($result);

        $row = $statement->fetch($pdo::FETCH_OBJ);

        static::assertIsObject($row);
        static::assertEquals((object) ['id' => 1, 'name' => 'john'], $row);

        $row = $statement->fetch($pdo::FETCH_OBJ);

        static::assertIsObject($row);
        static::assertEquals((object) ['id' => 2, 'name' => 'jane'], $row);

        $row = $statement->fetch($pdo::FETCH_OBJ);

        static::assertFalse($row);
    }
}
