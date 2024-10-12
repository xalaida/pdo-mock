<?php

namespace Tests\Xala\Elomock;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;

class PostDeleteBuilderTest extends TestCase
{
    #[Test]
    public function itShouldHandleDeleteQueriesOnFly(): void
    {
        $connection = $this->getFakeConnection();

        $connection->onDeleteQuery(fn () => 3);

        $result = (new Builder($connection))
            ->from('users')
            ->where(['id' => 7])
            ->delete();

        static::assertEquals(3, $result);

        $result = (new Builder($connection))
            ->from('posts')
            ->where(['id' => 1])
            ->delete();

        static::assertEquals(3, $result);
    }

    #[Test]
    public function itShouldVerifyExecutedQuery(): void
    {
        $connection = $this->getFakeConnection();

        $connection->onDeleteQuery(fn () => 1);

        $result = (new Builder($connection))
            ->from('users')
            ->where(['id' => 7])
            ->delete();

        static::assertEquals(1, $result);

        $connection->assertQueried('delete from "users" where ("id" = ?)', [7]);
    }

    #[Test]
    public function itShouldThrowExceptionWhenQueryWasntExecuted(): void
    {
        $connection = $this->getFakeConnection();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage("No queries were executed");

        $connection->assertQueried('delete from "users" where ("id" = ?)', [7]);
    }

    #[Test]
    public function itShouldThrowExceptionWhenQueryDoesntMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->onDeleteQuery(fn () => 1);

        $result = (new Builder($connection))
            ->from('users')
            ->where(['id' => 7])
            ->delete();

        static::assertEquals(1, $result);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Query does not match');

        $connection->assertQueried('delete from "posts" where ("id" = ?)', [7]);
    }

    #[Test]
    public function itShouldThrowExceptionWhenBindingsDontMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->onDeleteQuery(fn () => 1);

        $result = (new Builder($connection))
            ->from('users')
            ->where(['id' => 7])
            ->delete();

        static::assertEquals(1, $result);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Bindings do not match');

        $connection->assertQueried('delete from "users" where ("id" = ?)', [1]);
    }
}
