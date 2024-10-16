<?php

namespace Tests\Xala\Elomock;

use Generator;
use PHPUnit\Framework\Attributes\Test;

class CursorQueryTest extends TestCase
{
    #[Test]
    public function itShouldHandleCursor(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from "users"')
            ->andReturnRows([
                ['id' => 1, 'name' => 'xala'],
                ['id' => 2, 'name' => 'john'],
                ['id' => 3, 'name' => 'ryan'],
            ]);

        $users = $connection->cursor('select * from "users"');

        static::assertInstanceOf(Generator::class, $users);

        $users = iterator_to_array($users);

        static::assertCount(3, $users);
        static::assertEquals('xala', $users[0]->name);
        static::assertEquals('john', $users[1]->name);
        static::assertEquals('ryan', $users[2]->name);
    }
}
