<?php

namespace Tests\Xala\Elomock;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;

class FakeConnectionTest extends TestCase
{
    #[Test]
    public function itShouldVerifyQueries(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from "users"')
            ->andReturnRows([
                ['id' => 7, 'name' => 'test'],
            ]);

        $connection->expectQuery('update "users" set "name" = ? where ("id" = ?)')
            ->withBindings(['xala', 7]);

        $connection->expectQuery('select * from "users" where ("id" = ?) limit 1')
            ->withBindings([7])
            ->andReturnRows([
                ['id' => 7, 'name' => 'xala'],
            ]);

        $users = $connection
            ->table('users')
            ->get();

        static::assertCount(1, $users);
        static::assertEquals(7, $users[0]->id);
        static::assertEquals('test', $users[0]->name);

        $result = $connection
            ->table('users')
            ->where(['id' => 7])
            ->update(['name' => 'xala']);

        static::assertEquals(1, $result);

        $user = $connection
            ->table('users')
            ->where(['id' => 7])
            ->first();

        static::assertEquals('xala', $user->name);
    }
}
