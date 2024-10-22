<?php

namespace Tests\Xala\Elomock\Laravel;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;

class DeferUpdateQueryTest extends TestCase
{
    #[Test]
    public function itShouldVerifyDeferredQuery(): void
    {
        $connection = $this->getFakeConnection();

        $connection->deferWriteQueries();

        $result = $connection
            ->table('users')
            ->where(['id' => 7])
            ->update(['name' => 'xala']);

        static::assertEquals(1, $result);

        $connection->assertQueried('update "users" set "name" = ? where ("id" = ?)', ['xala', 7]);
    }

    #[Test]
    public function itShouldFailWhenQueryWasntExecuted(): void
    {
        $connection = $this->getFakeConnection();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage("No queries were executed");

        $connection->assertQueried('update "users" set "name" = ? where ("id" = ?)', [7]);
    }

    #[Test]
    public function itShouldFailWhenQueryDoesntMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->deferWriteQueries();

        $result = $connection
            ->table('users')
            ->where(['id' => 7])
            ->update(['name' => 'xala']);

        static::assertEquals(1, $result);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Query does not match');

        $connection->assertQueried('update "posts" set "name" = ? where ("id" = ?)', ['xala', 7]);
    }

    #[Test]
    public function itShouldFailWhenBindingsDontMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->deferWriteQueries();

        $result = $connection
            ->table('users')
            ->where(['id' => 7])
            ->update(['name' => 'xala']);

        static::assertEquals(1, $result);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query bindings: [update "users" set "name" = ? where ("id" = ?)] [xala, 7]');

        $connection->assertQueried('update "users" set "name" = ? where ("id" = ?)', ['xala', 5]);
    }
}
