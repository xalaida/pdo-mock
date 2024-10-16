<?php

namespace Tests\Xala\Elomock;

use Illuminate\Database\ConnectionInterface;
use PHPUnit\Framework\Attributes\Test;

class PretendTest extends TestCase
{
    #[Test]
    public function itShouldRunPretendQueries(): void
    {
        $connection = $this->getFakeConnection();

        $queries = $connection->pretend(function (ConnectionInterface $connection) {
            $connection->delete('delete from "users"');
            $connection->delete('delete from "orders"');
        });

        static::assertCount(2, $queries);
        static::assertEquals('delete from "users"', $queries[0]['query']);
        static::assertEquals('delete from "orders"', $queries[1]['query']);
    }
}
