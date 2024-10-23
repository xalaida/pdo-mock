<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ValueError;
use Xala\Elomock\FakePDO;

/**
 * @todo handle rewriting default fetch mode
 * @todo handle other fetch modes
 * @todo add ability to fetch from different sources (csv file, generator, from class objects, etc)
 */
class FetchAllTest extends TestCase
{
    #[Test]
    public function itShouldHandleFetchAll(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "users"')
            ->toBePrepared()
            ->andReturnRows([
                ['id' => 1, 'name' => 'john'],
                ['id' => 2, 'name' => 'jane'],
            ]);

        $statement = $pdo->prepare('select * from "users"');

        $result = $statement->execute();

        static::assertEquals(1, $result);

        $rows = $statement->fetchAll();

        static::assertCount(2, $rows);
        static::assertIsArray($rows[0]);
        static::assertEquals([0 => 1, 'id' => 1, 1 => 'john', 'name' => 'john'], $rows[0]);
        static::assertIsArray($rows[1]);
        static::assertEquals([0 => 2, 'id' => 2, 1 => 'jane', 'name' => 'jane'], $rows[1]);
    }

    #[Test]
    public function itShouldReturnEmptyRowsWhenStatementIsntExecuted(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "users"')
            ->toBePrepared()
            ->andReturnRows([
                ['id' => 1, 'name' => 'john'],
                ['id' => 2, 'name' => 'jane'],
            ]);

        $statement = $pdo->prepare('select * from "users"');

        $rows = $statement->fetchAll();

        static::assertEquals([], $rows);
    }

    #[Test]
    public function itShouldFailOnFetchAllInLazyMode(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "users"')
            ->toBePrepared()
            ->andReturnRows([
                ['id' => 1, 'name' => 'john'],
                ['id' => 2, 'name' => 'jane'],
            ]);

        $statement = $pdo->prepare('select * from "users"');

        $this->expectException(ValueError::class);

        $statement->fetchAll($pdo::FETCH_LAZY);
    }

    #[Test]
    public function itShouldHandleFetchAllInAssocMode(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "users"')
            ->toBePrepared()
            ->andReturnRows([
                ['id' => 1, 'name' => 'john'],
                ['id' => 2, 'name' => 'jane'],
            ]);

        $statement = $pdo->prepare('select * from "users"');

        $result = $statement->execute();

        static::assertEquals(1, $result);

        $rows = $statement->fetchAll($pdo::FETCH_ASSOC);

        static::assertCount(2, $rows);
        static::assertIsArray($rows[0]);
        static::assertEquals(['id' => 1, 'name' => 'john'], $rows[0]);
        static::assertIsArray($rows[1]);
        static::assertEquals(['id' => 2, 'name' => 'jane'], $rows[1]);
    }

    #[Test]
    public function itShouldHandleFetchAllInNumMode(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "users"')
            ->toBePrepared()
            ->andReturnRows([
                ['id' => 1, 'name' => 'john'],
                ['id' => 2, 'name' => 'jane'],
            ]);

        $statement = $pdo->prepare('select * from "users"');

        $result = $statement->execute();

        static::assertEquals(1, $result);

        $rows = $statement->fetchAll($pdo::FETCH_NUM);

        static::assertCount(2, $rows);
        static::assertIsArray($rows[0]);
        static::assertEquals([1, 'john'], $rows[0]);
        static::assertIsArray($rows[1]);
        static::assertEquals([2, 'jane'], $rows[1]);
    }

    #[Test]
    public function itShouldHandleFetchAllInBothMode(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "users"')
            ->toBePrepared()
            ->andReturnRows([
                ['id' => 1, 'name' => 'john'],
                ['id' => 2, 'name' => 'jane'],
            ]);

        $statement = $pdo->prepare('select * from "users"');

        $result = $statement->execute();

        static::assertEquals(1, $result);

        $rows = $statement->fetchAll($pdo::FETCH_BOTH);

        static::assertCount(2, $rows);
        static::assertIsArray($rows[0]);
        static::assertEquals([0 => 1, 'id' => 1, 1 => 'john', 'name' => 'john'], $rows[0]);
        static::assertIsArray($rows[1]);
        static::assertEquals([0 => 2, 'id' => 2, 1 => 'jane', 'name' => 'jane'], $rows[1]);
    }

    #[Test]
    public function itShouldHandleFetchAllInObjMode(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "users"')
            ->toBePrepared()
            ->andReturnRows([
                ['id' => 1, 'name' => 'john'],
                ['id' => 2, 'name' => 'jane'],
            ]);

        $statement = $pdo->prepare('select * from "users"');

        $result = $statement->execute();

        static::assertEquals(1, $result);

        $rows = $statement->fetchAll($pdo::FETCH_OBJ);

        static::assertCount(2, $rows);
        static::assertIsObject($rows[0]);
        static::assertEquals((object) ['id' => 1, 'name' => 'john'], $rows[0]);
        static::assertIsObject($rows[1]);
        static::assertEquals((object) ['id' => 2, 'name' => 'jane'], $rows[1]);
    }
}
