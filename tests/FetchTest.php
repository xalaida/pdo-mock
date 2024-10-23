<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Xala\Elomock\FakePDO;

class FetchTest extends TestCase
{
    #[Test]
    public function itShouldHandleReturnRowsUsingFetchAssocMode(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "users"')
            ->toBePrepared()
            ->andReturnRows([
                ['id' => 1, 'name' => 'xala'],
                ['id' => 2, 'name' => 'john'],
                ['id' => 3, 'name' => 'jane'],
            ]);

        $statement = $pdo->prepare('select * from "users"');

        $result = $statement->execute();

        $rows = $statement->fetchAll($pdo::FETCH_ASSOC);

        static::assertEquals(1, $result);
        static::assertCount(3, $rows);
        static::assertIsArray($rows[0]);
        static::assertEquals(1, $rows[0]['id']);
        static::assertEquals('xala', $rows[0]['name']);
        static::assertIsArray($rows[1]);
        static::assertEquals(2, $rows[1]['id']);
        static::assertEquals('john', $rows[1]['name']);
        static::assertIsArray($rows[2]);
        static::assertEquals(3, $rows[2]['id']);
        static::assertEquals('jane', $rows[2]['name']);
    }
}
