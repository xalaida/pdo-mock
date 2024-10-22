<?php

namespace Tests\Xala\Elomock\Laravel;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;

class RawQueryTest extends TestCase
{
    #[Test]
    public function itShouldVerifyRawStatements(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('delete from "users"')
            ->andReturnCount(3);

        $result = $connection->statement('delete from "users"');

        static::assertEquals(5, $result);
    }

    #[Test]
    public function itShouldVerifyRawQuery(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('delete from "users" where exists (select 1 from "orders" where "orders"."user_id" = "users"."id")')
            ->andReturnCount(3);

        $result = $connection
            ->table('users')
            ->whereExists(function (Builder $query) use ($connection) {
                $query->select($connection->raw(1))
                    ->from('orders')
                    ->whereColumn('orders.user_id', 'users.id');
            })
            ->delete();

        static::assertEquals(3, $result);

        $connection->assertExpectationsFulfilled();
    }

    #[Test]
    public function itShouldVerifyUnpreparedQuery(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('delete from "users" where "id" = 7')
            ->andReturnCount(1);

        $result = $connection->unprepared('delete from "users" where "id" = 7');

        static::assertEquals(1, $result);

        $connection->assertExpectationsFulfilled();
    }
}
