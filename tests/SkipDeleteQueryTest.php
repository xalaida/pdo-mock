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

        $connection->skipAffectingQueries();

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

        $connection->skipAffectingQueries();

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

        $connection->skipAffectingQueries();

        $result = (new Builder($connection))
            ->from('users')
            ->where(['id' => 7])
            ->delete();

        static::assertEquals(1, $result);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Bindings do not match');

        $connection->assertQueried('delete from "users" where ("id" = ?)', [1]);
    }

    #[Test]
    public function itShouldThrowExceptionWhenDeleteQueryWasntVerified(): void
    {
        $connection = $this->getFakeConnection();

        $connection->skipAffectingQueries();

        $result = (new Builder($connection))
            ->from('users')
            ->where(['id' => 7])
            ->delete();

        static::assertEquals(1, $result);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Some affecting queries were not fulfilled.');

        $connection->assertAffectingQueriesFulfilled();
    }
}
