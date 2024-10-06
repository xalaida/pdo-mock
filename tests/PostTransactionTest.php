<?php

namespace Tests\Xala\EloquentMock;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;

class PostTransactionTest extends TestCase
{
    #[Test]
    public function itShouldVerifyTransactionAfterExecution(): void
    {
        $connection = $this->getFakeConnection();

        // TODO: refactor this configuration using single method to handle all write queries automatically
        $connection->recordTransactions();
        $connection->onInsertQuery(fn () => 1);

        $connection->transaction(function () use ($connection) {
            (new Builder($connection))
                ->from('users')
                ->insert(['name' => 'xala']);
        });

        $connection->assertBeganTransaction();

        $connection->assertQueried('insert into "users" ("name") values (?)', ['xala']);

        $connection->assertCommitted();
    }

    #[Test]
    public function itShouldVerifyTransactionAfterExecutionUsingCallableSyntax(): void
    {
        $connection = $this->getFakeConnection();

        // TODO: refactor this configuration using single method to handle all write queries automatically
        $connection->recordTransactions();
        $connection->onInsertQuery(fn () => 1);

        $connection->transaction(function () use ($connection) {
            (new Builder($connection))
                ->from('users')
                ->insert(['name' => 'xala']);
        });

        $connection->assertTransaction(function () use ($connection) {
            $connection->assertQueried('insert into "users" ("name") values (?)', ['xala']);
        });
    }
}
