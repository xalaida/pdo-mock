<?php

namespace Tests\Xala\Elomock\Laravel;

use DateTime;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;

class QueryBindingsTest extends TestCase
{
    #[Test]
    public function itShouldValidatePreparedBindings(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from "users" where "created_at" > ?')
            ->withBindings(['2020-10-10 00:00:00'])
            ->andReturnRows([
                ['id' => 7, 'name' => 'xala'],
            ]);

        $results = $connection->table('users')
            ->where('created_at', '>', new DateTime('2020-10-10'))
            ->get();

        $this->assertCount(1, $results);
        $this->assertEquals('xala', $results[0]->name);
    }

    #[Test]
    public function itShouldVerifySelectQueryWithCallableBindings(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings(function (array $bindings) {
                self::assertEquals(7, $bindings[0]);
            })
            ->andReturnRows([
                ['id' => 7, 'name' => 'xala'],
            ]);

        $user = $connection
            ->table('users')
            ->find(7);

        static::assertEquals(7, $user->id);
        static::assertEquals('xala', $user->name);
    }

    #[Test]
    public function itShouldFailIfBindingsCallbackReturnsFalse(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings(function () {
                return false;
            })
            ->andReturnRows([
                ['id' => 7, 'name' => 'xala'],
            ]);

        $builder = $connection->table('users');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query bindings: [select * from "users" where "id" = ? limit 1] [7]');

        $builder->find(7);
    }

    #[Test]
    public function itShouldPassIfBindingsCallbackReturnsTrue(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings(function () {
                return true;
            })
            ->andReturnRows([
                ['id' => 7, 'name' => 'xala'],
            ]);

        $connection
            ->table('users')
            ->find(7);
    }

    #[Test]
    public function itShouldPassIfBindingsCallbackReturnsVoid(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings(function () {
                //
            })
            ->andReturnRows([
                ['id' => 7, 'name' => 'xala'],
            ]);

        $connection
            ->table('users')
            ->find(7);
    }
}
