<?php

namespace Tests\Xala\Elomock;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;

class QueryBindingsTest extends TestCase
{
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

        $user = (new Builder($connection))
            ->select('*')
            ->from('users')
            ->find(7);

        static::assertEquals(7, $user['id']);
        static::assertEquals('xala', $user['name']);
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

        $builder = (new Builder($connection))
            ->select('*')
            ->from('users');

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

        (new Builder($connection))
            ->select('*')
            ->from('users')
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

        (new Builder($connection))
            ->select('*')
            ->from('users')
            ->find(7);
    }
}
