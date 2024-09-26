<?php

namespace Tests\Xala\EloquentMock;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Xala\EloquentMock\FakeConnection;

class FakeConnectionTest extends TestCase
{
    #[Test]
    public function itShouldDatabaseQuery(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldPrepare('select * from "users"')
            ->andReturnRows([
                ['id' => 1, 'name' => 'xala'],
                ['id' => 2, 'name' => 'john'],
                ['id' => 3, 'name' => 'ryan'],
            ]);

        $builder = new Builder($connection);

        $users = $builder
            ->select('*')
            ->from('users')
            ->get();

        static::assertCount(3, $users);
        static::assertEquals('xala', $users[0]['name']);
        static::assertEquals('john', $users[1]['name']);
        static::assertEquals('ryan', $users[2]['name']);
    }

    protected function getFakeConnection(): FakeConnection
    {
        return new FakeConnection();
    }
}
