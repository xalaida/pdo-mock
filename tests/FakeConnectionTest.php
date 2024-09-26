<?php

namespace Tests\Xala\EloquentMock;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Xala\EloquentMock\FakeConnection;

class FakeConnectionTest extends TestCase
{
    #[Test]
    public function itShouldVerifyBaseSelectQuery(): void
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

    #[Test]
    public function itReturnsNoRowsForSelectQueryByDefault(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldPrepare('select * from "users"');

        $builder = new Builder($connection);

        $users = $builder
            ->select('*')
            ->from('users')
            ->get();

        static::assertEmpty($users);
    }

    #[Test]
    public function itThrowsExceptionOnUnexpectedSelectQuery(): void
    {
        $connection = $this->getFakeConnection();

        $builder = new Builder($connection);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unexpected select query: [select * from "users"]');

        $builder
            ->select('*')
            ->from('users')
            ->get();
    }

    protected function getFakeConnection(): FakeConnection
    {
        return new FakeConnection();
    }
}
