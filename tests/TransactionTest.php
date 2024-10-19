<?php

namespace Tests\Xala\Elomock;

use Illuminate\Database\DatabaseTransactionsManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use RuntimeException;

class TransactionTest extends TestCase
{
    // TODO: verify savepoints

    #[Test]
    public function itShouldVerifyTransaction(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectBeginTransaction();

        $connection->expectQuery('insert into "users" ("name") values (?)');

        $connection->expectCommit();

        $connection->beginTransaction();

        $connection
            ->table('users')
            ->insert(['name' => 'john']);

        $connection->commit();

        $connection->assertExpectationsFulfilled();
    }

    #[Test]
    public function itShouldFailWhenTransactionWasntCreated(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectBeginTransaction();

        $connection->expectQuery('insert into "users" ("name") values (?)');

        $connection->expectCommit();

        $builder = $connection
            ->table('users');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query: [insert into "users" ("name") values (?)] [john]');

        $builder->insert(['name' => 'john']);
    }

    #[Test]
    public function itShouldVerifyTransactionUsingCallableSyntax(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectTransaction(function ($connection) {
            $connection->expectQuery('insert into "users" ("name") values (?)');
            $connection->expectQuery('insert into "posts" ("title") values (?)');
        });

        $connection->transaction(function () use ($connection) {
            $connection
                ->table('users')
                ->insert(['name' => 'john']);

            $connection
                ->table('posts')
                ->insert(['title' => 'john']);
        });

        $connection->assertExpectationsFulfilled();
    }

    #[Test]
    public function itShouldFailWhenTransactionalQueryWasntExecuted(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectTransaction(function ($connection) {
            $connection->expectQuery('insert into "users" ("name") values (?)');
            $connection->expectQuery('insert into "posts" ("title") values (?)');
        });

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected PDO::commit()');

        $connection->transaction(function () use ($connection) {
            $connection
                ->table('users')
                ->insert(['name' => 'john']);
        });
    }

    #[Test]
    public function itexpectRollbackTransaction(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectBeginTransaction();

        $connection->expectQuery('insert into "users" ("name") values (?)');

        $connection->expectRollback();

        try {
            $connection->transaction(function () use ($connection) {
                $connection
                    ->table('users')
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

        $connection->expectBeginTransaction();

        $connection->expectCommit();

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

        $connection->expectBeginTransaction();

        $connection->expectRollback();

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

        $connection->expectQuery('insert into "users" ("name") values (?)');

        $connection->beginTransaction();

        $connection
            ->table('users')
            ->insert(['name' => 'john']);

        $connection->commit();

        $connection->assertExpectationsFulfilled();
    }

    #[Test]
    public function itShouldNotRollbackIfExpectationFails(): void
    {
        $connection = $this->getFakeConnection();

        $connection->ignoreTransactions();

        $connection->expectQuery('insert into "users" ("name") values (?)');

        $connection->beginTransaction();

        $connection
            ->table('users')
            ->insert(['name' => 'john']);

        $connection->commit();

        $connection->assertExpectationsFulfilled();
    }
}
