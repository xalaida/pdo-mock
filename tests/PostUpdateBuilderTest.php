<?php

namespace Tests\Xala\EloquentMock;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;

class PostUpdateBuilderTest extends TestCase
{
    #[Test]
    public function itShouldHandleQueriesOnFly(): void
    {
        $connection = $this->getFakeConnection();

        $connection->onUpdateQuery(fn () => 5);

        $result = (new Builder($connection))
            ->from('users')
            ->where(['id' => 5])
            ->update(['name' => 'john']);

        static::assertEquals(5, $result);

        $result = (new Builder($connection))
            ->from('posts')
            ->where(['id' => 6])
            ->update(['name' => 'jane']);

        static::assertEquals(5, $result);
    }

    #[Test]
    public function itShouldVerifyExecutedQuery(): void
    {
        $connection = $this->getFakeConnection();

        $connection->onUpdateQuery(fn () => 1);

        $result = (new Builder($connection))
            ->from('users')
            ->where(['id' => 7])
            ->update(['name' => 'xala']);

        static::assertEquals(1, $result);

        $connection->assertQueried('update "users" set "name" = ? where ("id" = ?)', ['xala', 7]);
    }

    #[Test]
    public function itShouldThrowExceptionWhenQueryWasntExecuted(): void
    {
        $connection = $this->getFakeConnection();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage("No queries were executed");

        $connection->assertQueried('update "users" set "name" = ? where ("id" = ?)', [7]);
    }

    #[Test]
    public function itShouldThrowExceptionWhenQueryDoesntMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->onUpdateQuery(fn () => 1);

        $result = (new Builder($connection))
            ->from('users')
            ->where(['id' => 7])
            ->update(['name' => 'xala']);

        static::assertEquals(1, $result);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Query does not match');

        $connection->assertQueried('update "posts" set "name" = ? where ("id" = ?)', ['xala', 7]);
    }

    #[Test]
    public function itShouldThrowExceptionWhenBindingsDontMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->onUpdateQuery(fn () => 1);

        $result = (new Builder($connection))
            ->from('users')
            ->where(['id' => 7])
            ->update(['name' => 'xala']);

        static::assertEquals(1, $result);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Bindings do not match');

        $connection->assertQueried('update "users" set "name" = ? where ("id" = ?)', ['xala', 5]);
    }
}
