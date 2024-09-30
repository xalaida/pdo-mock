<?php

namespace Tests\Xala\EloquentMock;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Xala\EloquentMock\FakeConnection;

class SelectBuilderTest extends TestCase
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

        $users = (new Builder($connection))
            ->select('*')
            ->from('users')
            ->get();

        static::assertCount(3, $users);
        static::assertEquals('xala', $users[0]['name']);
        static::assertEquals('john', $users[1]['name']);
        static::assertEquals('ryan', $users[2]['name']);
    }

    #[Test]
    public function itShouldThrowExceptionWhenQueryDoesNotMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('select * from "users"')
            ->andReturnRows([
                ['id' => 1, 'name' => 'xala'],
            ]);

        $builder = (new Builder($connection))
            ->select('*')
            ->from('posts');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unexpected select query: [select * from "posts"]');

        $builder->get();
    }

    #[Test]
    public function itReturnsNoRowsForSelectQueryByDefault(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('select * from "users"');

        $users = (new Builder($connection))
            ->select('*')
            ->from('users')
            ->get();

        static::assertEmpty($users);
    }

    #[Test]
    public function itShouldThrowExceptionOnUnexpectedSelectQuery(): void
    {
        $connection = $this->getFakeConnection();

        $builder = (new Builder($connection))
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

        $user = (new Builder($connection))
            ->select('*')
            ->from('users')
            ->find(7);

        static::assertEquals(7, $user['id']);
        static::assertEquals('xala', $user['name']);
    }

    #[Test]
    public function itShouldThrowExceptionWhenExpectedBindingsAreMissing(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('select * from "users" where "id" = ? limit 1')
            ->andReturnRows([
                ['id' => 7, 'name' => 'xala'],
            ]);

        $builder = (new Builder($connection))
            ->select('*')
            ->from('users');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unexpected select query bindings: [select * from "users" where "id" = ? limit 1] [7]');

        $builder->find(7);
    }

    #[Test]
    public function itShouldThrowExceptionOnSelectQueryWithUnexpectedBindings(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings([1]);

        $builder = (new Builder($connection))
            ->select('*')
            ->from('users');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unexpected select query bindings: [select * from "users" where "id" = ? limit 1] [7]');

        $builder->find(7);
    }

    #[Test]
    public function itShouldValidateSelectQueryWithAnyBindings(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('select * from "users" where "id" = ? limit 1')
            ->withAnyBindings()
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
    public function itShouldValidateMultipleSelectQueries(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings([1])
            ->andReturnRows([
                ['id' => 1, 'name' => 'john'],
            ]);

        $connection->shouldQuery('select * from "posts" where ("user_id" = ?)')
            ->withBindings([1])
            ->andReturnRows([
                ['id' => 1, 'user_id' => 1, 'title' => 'PHP'],
                ['id' => 2, 'user_id' => 1, 'title' => 'Laravel'],
                ['id' => 3, 'user_id' => 1, 'title' => 'Eloquent'],
            ]);

        $user = (new Builder($connection))
            ->select('*')
            ->from('users')
            ->find(1);

        $posts = (new Builder($connection))
            ->select('*')
            ->from('posts')
            ->where(['user_id' => 1])
            ->get();

        static::assertEquals('john', $user['name']);
        static::assertCount(3, $posts);
        static::assertEquals('PHP', $posts[0]['title']);
        static::assertEquals('Laravel', $posts[1]['title']);
        static::assertEquals('Eloquent', $posts[2]['title']);
    }

    #[Test]
    public function itShouldValidateMultipleSameSelectQueriesWithDifferentBindings(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings([1])
            ->andReturnRows([
                ['id' => 1, 'name' => 'john'],
            ]);

        $connection->shouldQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings([2])
            ->andReturnRows([
                ['id' => 2, 'name' => 'jane'],
            ]);

        $john = (new Builder($connection))
            ->select('*')
            ->from('users')
            ->find(1);

        $jane = (new Builder($connection))
            ->select('*')
            ->from('users')
            ->find(2);

        static::assertEquals('john', $john['name']);
        static::assertEquals('jane', $jane['name']);
    }

    #[Test]
    public function itShouldThrowExceptionWhenMultipleSameSelectQueriesWithDifferentBindingsAreExecutedInIncorrectOrder(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings([1])
            ->andReturnRows([
                ['id' => 1, 'name' => 'john'],
            ]);

        $connection->shouldQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings([2])
            ->andReturnRows([
                ['id' => 2, 'name' => 'jane'],
            ]);

        $builder = (new Builder($connection))
            ->select('*')
            ->from('users');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unexpected select query bindings: [select * from "users" where "id" = ? limit 1] [2]');

        $builder->find(2);
    }

    #[Test]
    public function itShouldThrowExceptionWhenQueryWasntExecuted(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings([1])
            ->andReturnRows([
                ['id' => 1, 'name' => 'john'],
            ]);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage("Some queries were not executed: 1\nFailed asserting that an array is empty.");

        $connection->assertExpectedQueriesExecuted();
    }

    protected function getFakeConnection(): FakeConnection
    {
        return new FakeConnection();
    }
}
