<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;

class SelectQueryTest extends TestCase
{
    #[Test]
    public function itShouldVerifySelectQuery(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from "users"')
            ->andReturnRows([
                ['id' => 1, 'name' => 'xala'],
                ['id' => 2, 'name' => 'john'],
                ['id' => 3, 'name' => 'ryan'],
            ]);

        $users = $connection
            ->table('users')
            ->get();

        static::assertCount(3, $users);
        static::assertEquals('xala', $users[0]->name);
        static::assertEquals('john', $users[1]->name);
        static::assertEquals('ryan', $users[2]->name);
    }

    #[Test]
    public function itShouldSelectOneRow(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from users where id = ?', [5])
            ->andReturnRow([
                'id' => 1,
                'name' => 'xala',
            ]);

        $user = $connection->selectOne('select * from users where id = ?', [5]);

        static::assertEquals(1, $user->id);
        static::assertEquals('xala', $user->name);
    }

    #[Test]
    public function itShouldSelectRowsUsingCallableSyntax(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from users where id = ?', [5])
            ->andReturnRowsUsing(function (array $bindings) {
                return [
                    (object) ['id' => $bindings[0]],
                ];
            });

        $user = $connection->selectOne('select * from users where id = ?', [5]);

        static::assertEquals(5, $user->id);
    }

    #[Test]
    public function itShouldFailWhenQueryDoesNotMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from "users"')
            ->andReturnRows([
                ['id' => 1, 'name' => 'xala'],
            ]);

        $builder = $connection->table('posts');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query: [select * from "posts"]');

        $builder->get();
    }

    #[Test]
    public function itReturnsNoRowsForSelectQueryByDefault(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from "users"');

        $users = $connection
            ->table('users')
            ->get();

        static::assertEmpty($users);
    }

    #[Test]
    public function itShouldFailOnUnexpectedSelectQuery(): void
    {
        $connection = $this->getFakeConnection();

        $builder = $connection->table('users');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query: [select * from "users"]');

        $builder->get();
    }

    #[Test]
    public function itShouldVerifySelectQueryWithBindings(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings([7])
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
    public function itShouldFailWhenExpectedBindingsAreMissing(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings([])
            ->andReturnRows([
                ['id' => 7, 'name' => 'xala'],
            ]);

        $builder = $connection->table('users');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query bindings: [select * from "users" where "id" = ? limit 1] [7]');

        $builder->find(7);
    }

    #[Test]
    public function itShouldFailOnSelectQueryWithUnexpectedBindings(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings([1]);

        $builder = $connection->table('users');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query bindings: [select * from "users" where "id" = ? limit 1] [7]');

        $builder->find(7);
    }

    #[Test]
    public function itShouldValidateSelectQueryWithAnyBindings(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from "users" where "id" = ? limit 1')
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
    public function itShouldValidateMultipleSelectQueries(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings([1])
            ->andReturnRows([
                ['id' => 1, 'name' => 'john'],
            ]);

        $connection->expectQuery('select * from "posts" where ("user_id" = ?)')
            ->withBindings([1])
            ->andReturnRows([
                ['id' => 1, 'user_id' => 1, 'title' => 'PHP'],
                ['id' => 2, 'user_id' => 1, 'title' => 'Laravel'],
                ['id' => 3, 'user_id' => 1, 'title' => 'Eloquent'],
            ]);

        $user = $connection
            ->table('users')
            ->find(1);

        $posts = $connection
            ->table('posts')
            ->where(['user_id' => 1])
            ->get();

        static::assertEquals('john', $user->name);
        static::assertCount(3, $posts);
        static::assertEquals('PHP', $posts[0]->title);
        static::assertEquals('Laravel', $posts[1]->title);
        static::assertEquals('Eloquent', $posts[2]->title);
    }

    #[Test]
    public function itShouldValidateMultipleSameSelectQueriesWithDifferentBindings(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings([1])
            ->andReturnRows([
                ['id' => 1, 'name' => 'john'],
            ]);

        $connection->expectQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings([2])
            ->andReturnRows([
                ['id' => 2, 'name' => 'jane'],
            ]);

        $john = $connection
            ->table('users')
            ->find(1);

        $jane = $connection
            ->table('users')
            ->find(2);

        static::assertEquals('john', $john->name);
        static::assertEquals('jane', $jane->name);
    }

    #[Test]
    public function itShouldFailWhenMultipleQueriesWithDifferentBindingsAreExecutedInIncorrectOrder(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings([1])
            ->andReturnRows([
                ['id' => 1, 'name' => 'john'],
            ]);

        $connection->expectQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings([2])
            ->andReturnRows([
                ['id' => 2, 'name' => 'jane'],
            ]);

        $builder = $connection->table('users');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query bindings: [select * from "users" where "id" = ? limit 1] [2]');

        $builder->find(2);
    }

    #[Test]
    public function itShouldReturnNothing(): void
    {
         $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings([7])
            ->andReturnNothing();

        $result = $connection
            ->table('users')
            ->find(7);

        static::assertNull($result);
    }

    #[Test]
    public function itShouldFailWhenQueryWasntExecuted(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings([1])
            ->andReturnRows([
                ['id' => 1, 'name' => 'john'],
            ]);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Some expectations were not fulfilled.');

        $connection->assertExpectationsFulfilled();
    }
}
