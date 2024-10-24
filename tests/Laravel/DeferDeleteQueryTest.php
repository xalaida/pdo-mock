<?php

namespace Tests\Xala\Elomock\Laravel;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;

class DeferDeleteQueryTest extends TestCase
{
    #[Test]
    public function itShouldVerifyDeferredQueries(): void
    {
        $connection = $this->getFakeConnection();

        $connection->deferWriteQueries();

        $result = $connection
            ->table('users')
            ->where(['id' => 7])
            ->delete();

        static::assertEquals(1, $result);

        $connection->assertQueried('delete from "users" where ("id" = ?)', [7]);
    }

    #[Test]
    public function itShouldFailWhenQueryWasntExecuted(): void
    {
        $connection = $this->getFakeConnection();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage("No queries were executed");

        $connection->assertQueried('delete from "users" where ("id" = ?)', [7]);
    }

    #[Test]
    public function itShouldFailWhenQueryDoesntMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->deferWriteQueries();

        $result = $connection
            ->table('users')
            ->where(['id' => 7])
            ->delete();

        static::assertEquals(1, $result);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Query does not match');

        $connection->assertQueried('delete from "posts" where ("id" = ?)', [7]);
    }

    #[Test]
    public function itShouldFailWhenBindingsDontMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->deferWriteQueries();

        $result = $connection
            ->table('users')
            ->where(['id' => 7])
            ->delete();

        static::assertEquals(1, $result);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query bindings: [delete from "users" where ("id" = ?)] [7]');

        $connection->assertQueried('delete from "users" where ("id" = ?)', [1]);
    }

    #[Test]
    public function itShouldFailWhenDeleteQueryWasntVerified(): void
    {
        $connection = $this->getFakeConnection();

        $connection->deferWriteQueries();

        $result = $connection
            ->table('users')
            ->where(['id' => 7])
            ->delete();

        static::assertEquals(1, $result);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Some write queries were not fulfilled');

        $connection->assertDeferredQueriesVerified();
    }
}