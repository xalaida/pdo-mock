<?php

namespace Tests\Xala\Elomock;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;

class SkipInsertQueryTest extends TestCase
{
    #[Test]
    public function itShouldVerifySkippedQuery(): void
    {
        $connection = $this->getFakeConnection();

        $connection->skipWriteQueries();

        $result = (new Builder($connection))
            ->from('users')
            ->insert(['name' => 'xala']);

        static::assertTrue($result);

        $connection->assertQueried('insert into "users" ("name") values (?)', ['xala']);
    }

    #[Test]
    public function itShouldFailWhenQueryWasntExecuted(): void
    {
        $connection = $this->getFakeConnection();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage("No queries were executed");

        $connection->assertQueried('insert into "users" ("name") values (?)', ['xala']);
    }

    #[Test]
    public function itShouldFailWhenQueryDoesntMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->skipWriteQueries();

        $result = (new Builder($connection))
            ->from('users')
            ->insert(['name' => 'xala']);

        static::assertEquals(1, $result);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Query does not match');

        $connection->assertQueried('insert into "users" ("email") values (?)', ['xala']);
    }

    #[Test]
    public function itShouldFailWhenBindingsDontMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->skipWriteQueries();

        $result = (new Builder($connection))
            ->from('users')
            ->insert(['name' => 'xala']);

        static::assertEquals(1, $result);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query bindings: [insert into "users" ("name") values (?)] [xala]');

        $connection->assertQueried('insert into "users" ("name") values (?)', ['john']);
    }

    #[Test]
    public function itShouldFailWhenInsertQueryWasntVerified(): void
    {
        $connection = $this->getFakeConnection();

        $connection->skipWriteQueries();

        $result = (new Builder($connection))
            ->from('users')
            ->insert(['name' => 'xala']);

        static::assertTrue($result);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Some write queries were not fulfilled');

        $connection->assertWriteQueriesFulfilled();
    }
}