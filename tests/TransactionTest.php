<?php

namespace Tests\Xala\EloquentMock;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;

class TransactionTest extends TestCase
{
    #[Test]
    public function itShouldVerifyTransaction(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldBeginTransaction();

        $connection->shouldQuery('insert into "users" ("name") values (?)')
            ->withAnyBindings();

        $connection->shouldCommit();

        $connection->beginTransaction();

        (new Builder($connection))
            ->from('users')
            ->insert(['name' => 'john']);

        $connection->commit();

        $connection->assertExpectedQueriesExecuted();
    }

    #[Test]
    public function itShouldThrowExceptionWhenTransactionWasntCreated(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldBeginTransaction();

        $connection->shouldQuery('insert into "users" ("name") values (?)')
            ->withAnyBindings();

        $connection->shouldCommit();

        $builder = (new Builder($connection))
            ->from('users');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected insert query: [insert into "users" ("name") values (?)] [john]');

        $builder->insert(['name' => 'john']);
    }
}
