<?php

namespace Tests\Xala\Elomock;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use PHPUnit\Framework\Attributes\Test;

class RawQueryTest extends TestCase
{
    #[Test]
    public function itShouldVerifyRawStatements(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('delete from "users"')
            ->andAffectCount(3);

        $result = $connection->statement('delete from "users"');

        static::assertEquals(5, $result);
    }

    #[Test]
    public function itShouldVerifyRawQuery(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('delete from "users" where exists (select 1 from "orders" where "orders"."user_id" = "users"."id")')
            ->andAffectCount(3);

        $result = (new Builder($connection))
            ->from('users')
            ->whereExists(function (Builder $query) {
                $query->select(new Expression(1))
                    ->from('orders')
                    ->whereColumn('orders.user_id', 'users.id');
            })
            ->delete();

        static::assertEquals(3, $result);

        $connection->assertExpectationsFulfilled();
    }
}
