<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Xala\Elomock\FakePDO;

class FetchTest extends TestCase
{
    #[Test]
    public function itShouldHandleFetch(): void
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

        $row = $statement->fetch($pdo::FETCH_OBJ);
        static::assertIsObject($row);
        static::assertEquals($row, (object) ['id' => 1, 'name' => 'john']);

        $row = $statement->fetch($pdo::FETCH_OBJ);
        static::assertIsObject($row);
        static::assertEquals($row, (object) ['id' => 2, 'name' => 'jane']);

        $row = $statement->fetch($pdo::FETCH_OBJ);
        static::assertFalse($row);
    }

    #[Test]
    public function itShouldHandleReturnRowsUsingFetchAllInAssocMode(): void
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

        $rows = $statement->fetchAll($pdo::FETCH_ASSOC);

        static::assertEquals(1, $result);
        static::assertCount(2, $rows);
        static::assertIsArray($rows[0]);
        static::assertEquals(1, $rows[0]['id']);
        static::assertEquals('john', $rows[0]['name']);
        static::assertIsArray($rows[1]);
        static::assertEquals(2, $rows[1]['id']);
        static::assertEquals('jane', $rows[1]['name']);
    }

    #[Test]
    public function itShouldHandleReturnRowsUsingFetchAllInObjMode(): void
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

        $rows = $statement->fetchAll($pdo::FETCH_OBJ);

        static::assertEquals(1, $result);
        static::assertCount(2, $rows);
        static::assertIsObject($rows[0]);
        static::assertEquals(1, $rows[0]->id);
        static::assertEquals('john', $rows[0]->name);
        static::assertIsObject($rows[1]);
        static::assertEquals(2, $rows[1]->id);
        static::assertEquals('jane', $rows[1]->name);
    }
}
