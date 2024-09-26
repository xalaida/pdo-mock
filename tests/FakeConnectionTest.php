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
    public function itShouldVerifySelectQuery(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('select * from "users"')
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

        $connection->shouldQuery('select * from "users"');

        $builder = new Builder($connection);

        $users = $builder
            ->select('*')
            ->from('users')
            ->get();

        static::assertEmpty($users);
    }

    #[Test]
    public function itShouldThrowExceptionOnUnexpectedSelectQuery(): void
    {
        $connection = $this->getFakeConnection();

        $builder = new Builder($connection);

        $builder
            ->select('*')
            ->from('users');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unexpected select query: [select * from "users"]');

        $builder->get();
    }

    #[Test]
    public function itShouldVerifySelectQueryWithBindings(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings([7])
            ->andReturnRows([
                ['id' => 7, 'name' => 'xala'],
            ]);

        $builder = new Builder($connection);

        $user = $builder
            ->select('*')
            ->from('users')
            ->find(7);

        static::assertEquals(7, $user['id']);
        static::assertEquals('xala', $user['name']);
    }

    #[Test]
    public function itShouldThrowExceptionOnSelectQueryWithUnexpectedBindings(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings([1]);

        $builder = new Builder($connection);

        $builder
            ->select('*')
            ->from('users');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unexpected select query bindings: [select * from "users" where "id" = ? limit 1] [7]');

        $builder->find(7);
    }

    #[Test]
    public function itShouldValidateMultipleSelectQueries(): void
    {
        $this->markTestSkipped('TODO');
    }

    #[Test]
    public function itShouldValidateSelectQueryWithAnyBindings(): void
    {
        $this->markTestSkipped('TODO');
    }

    #[Test]
    public function itShouldValidateMultipleSameSelectQueriesWithDifferentBindings(): void
    {
        $this->markTestSkipped('TODO');
    }

    #[Test]
    public function itShouldThrowExceptionWhenMultipleSameSelectQueriesWithDifferentBindingsAreCalledInIncorrectOrder(): void
    {
        $this->markTestSkipped('TODO');
    }

    #[Test]
    public function itShouldValidateSelectUpdateSelectQueries(): void
    {
        $this->markTestSkipped('TODO');
    }

    protected function getFakeConnection(): FakeConnection
    {
        return new FakeConnection();
    }
}
