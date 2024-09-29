<?php

namespace Tests\Xala\EloquentMock;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Xala\EloquentMock\FakeConnection;

class FakeConnectionTest extends TestCase
{
    #[Test]
    public function itShouldVerifyQueries(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('select * from "users"')
            ->andReturnRows([
                ['id' => 7, 'name' => 'test'],
            ]);

        $connection->shouldQuery('update "users" set "name" = ? where ("id" = ?)')
            ->withBindings(['xala', 7]);

        $connection->shouldQuery('select * from "users" where ("id" = ?) limit 1')
            ->withBindings([7])
            ->andReturnRows([
                ['id' => 7, 'name' => 'xala'],
            ]);

        $users = (new Builder($connection))
            ->select('*')
            ->from('users')
            ->get();

        static::assertCount(1, $users);
        static::assertEquals(7, $users[0]['id']);
        static::assertEquals('test', $users[0]['name']);

        $result = (new Builder($connection))
            ->from('users')
            ->where(['id' => 7])
            ->update(['name' => 'xala']);

        static::assertEquals(1, $result);

        $user = (new Builder($connection))
            ->select('*')
            ->from('users')
            ->where(['id' => 7])
            ->first();

        static::assertEquals('xala', $user['name']);
    }

    protected function getFakeConnection(): FakeConnection
    {
        return new FakeConnection();
    }
}
