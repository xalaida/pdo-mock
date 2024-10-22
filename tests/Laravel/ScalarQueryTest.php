<?php

namespace Tests\Xala\Elomock\Laravel;

use PHPUnit\Framework\Attributes\Test;

class ScalarQueryTest extends TestCase
{
    #[Test]
    public function itShouldHandleScalarQuery(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select count(*) from "users"')
            ->andReturnRows([
                ['count' => 5],
            ]);

        $value = $connection->scalar('select count(*) from "users"');

        static::assertEquals(5, $value);
    }
}
