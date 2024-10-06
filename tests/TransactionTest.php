<?php

namespace Tests\Xala\EloquentMock;

use Illuminate\Database\DatabaseTransactionsManager;
use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use RuntimeException;

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

        $connection->assertExpectationsFulfilled();
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

    #[Test]
    public function itShouldVerifyTransactionUsingCallableSyntax(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectTransaction(function ($connection) {
            $connection->shouldQuery('insert into "users" ("name") values (?)')
                ->withAnyBindings();

            $connection->shouldQuery('insert into "posts" ("title") values (?)')
                ->withAnyBindings();
        });

        $connection->transaction(function () use ($connection) {
            (new Builder($connection))
                ->from('users')
                ->insert(['name' => 'john']);

            (new Builder($connection))
                ->from('posts')
                ->insert(['title' => 'john']);
        });

        $connection->assertExpectationsFulfilled();
    }

    #[Test]
    public function itShouldThrowExceptionWhenTransactionalQueryWasntExecuted(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectTransaction(function ($connection) {
            $connection->shouldQuery('insert into "users" ("name") values (?)')
                ->withAnyBindings();

            $connection->shouldQuery('insert into "posts" ("title") values (?)')
                ->withAnyBindings();
        });

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected PDO::commit()');

        $connection->transaction(function () use ($connection) {
            (new Builder($connection))
                ->from('users')
                ->insert(['name' => 'john']);
        });
    }

    #[Test]
    public function itShouldRollbackTransaction(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldBeginTransaction();

        $connection->shouldQuery('insert into "users" ("name") values (?)')
            ->withAnyBindings();

        $connection->shouldRollback();

        try {
            $connection->transaction(function () use ($connection) {
                (new Builder($connection))
                    ->from('users')
                    ->insert(['name' => 'john']);

                throw new RuntimeException('Something went wrong');
            });
        } catch (RuntimeException $e) {
            $connection->assertExpectationsFulfilled();
        }
    }

    #[Test]
    public function itShouldExecuteCallbackAfterCommit(): void
    {
        $connection = $this->getFakeConnection();

        $connection->setTransactionManager(new DatabaseTransactionsManager());

        $connection->shouldBeginTransaction();

        $connection->shouldCommit();

        $connection->beginTransaction();

        $wasChangedAfterCommit = false;

        $connection->afterCommit(function () use (&$wasChangedAfterCommit) {
            $wasChangedAfterCommit = true;
        });

        $connection->commit();

        $this->assertTrue($wasChangedAfterCommit);
    }

    #[Test]
    public function itShouldntExecuteAfterCommitCallbackWhenTransactionFails(): void
    {
        $connection = $this->getFakeConnection();

        $connection->setTransactionManager(new DatabaseTransactionsManager());

        $connection->shouldBeginTransaction();

        $connection->shouldRollback();

        $connection->beginTransaction();

        $wasChangedAfterCommit = false;

        $connection->afterCommit(function () use (&$wasChangedAfterCommit) {
            $wasChangedAfterCommit = true;
        });

        $connection->rollback();

        $this->assertFalse($wasChangedAfterCommit);
    }

    #[Test]
    public function itShouldIgnoreTransactions(): void
    {
        $connection = $this->getFakeConnection();

        $connection->ignoreTransactions();

        $connection->shouldQuery('insert into "users" ("name") values (?)')
            ->withAnyBindings();

        $connection->beginTransaction();

        (new Builder($connection))
            ->from('users')
            ->insert(['name' => 'john']);

        $connection->commit();

        $connection->assertExpectationsFulfilled();
    }
}
