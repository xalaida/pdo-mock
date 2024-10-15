<?php

namespace Tests\Xala\Elomock;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;

class SkipDeleteQueryTest extends TestCase
{
    #[Test]
    public function itShouldVerifySkippedQueries(): void
    {
        $connection = $this->getFakeConnection();

        $connection->skipWriteQueries();

        $result = (new Builder($connection))
            ->from('users')
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

        $connection->skipWriteQueries();

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
    public function itShouldFailWhenBindingsDontMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->skipWriteQueries();

        $result = (new Builder($connection))
            ->from('users')
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

        $connection->skipWriteQueries();

        $result = (new Builder($connection))
            ->from('users')
            ->where(['id' => 7])
            ->delete();

        static::assertEquals(1, $result);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Some write queries were not fulfilled');

        $connection->assertWriteQueriesFulfilled();
    }
}
