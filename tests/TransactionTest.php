<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Xala\Elomock\PDOMock;

/**
 * @todo handle nested transactions
 * @todo add ability to ignore rolled back queries
 */
class TransactionTest extends TestCase
{
    #[Test]
    public function itShouldExecuteQueryInTransaction(): void
    {
        $pdo = new PDOMock();

        $pdo->expectBeginTransaction();
        $pdo->expectQuery('insert into "users" ("name") values ("john")');
        $pdo->expectCommit();

        static::assertFalse($pdo->inTransaction());

        $pdo->beginTransaction();

        $pdo->exec('insert into "users" ("name") values ("john")');

        static::assertTrue($pdo->inTransaction());

        $pdo->commit();

        static::assertFalse($pdo->inTransaction());

        $pdo->assertExpectationsFulfilled();
    }

    #[Test]
    public function itShouldRollbackTransaction(): void
    {
        $pdo = new PDOMock();

        $pdo->expectBeginTransaction();
        $pdo->expectQuery('insert into "users" ("name") values ("john")');
        $pdo->expectRollback();

        $pdo->beginTransaction();
        $pdo->exec('insert into "users" ("name") values ("john")');
        $pdo->rollBack();

        $pdo->assertExpectationsFulfilled();
    }

    #[Test]
    public function itShouldFailWhenQueryExecutedWithoutTransaction(): void
    {
        $pdo = new PDOMock();

        $pdo->expectBeginTransaction();

        $pdo->expectQuery('insert into "users" ("name") values ("john")');

        $this->expectException(ExpectationFailedException::class);

        $pdo->exec('insert into "users" ("name") values ("john")');
    }

    #[Test]
    public function itShouldExpectTransactionUsingCallableSyntax(): void
    {
        $pdo = new PDOMock();

        $pdo->expectTransaction(function () use ($pdo) {
            $pdo->expectQuery('insert into "users" ("name") values ("john")');
            $pdo->expectQuery('insert into "users" ("name") values ("jane")');
        });

        $pdo->beginTransaction();
        $pdo->exec('insert into "users" ("name") values ("john")');
        $pdo->exec('insert into "users" ("name") values ("jane")');
        $pdo->commit();

        $pdo->assertExpectationsFulfilled();
    }

    #[Test]
    public function itShouldFailWhenTransactionalQueryIsNotExecuted(): void
    {
        $pdo = new PDOMock();

        $pdo->expectTransaction(function () use ($pdo) {
            $pdo->expectQuery('insert into "users" ("name") values ("john")');
            $pdo->expectQuery('insert into "users" ("name") values ("jane")');
        });

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected PDO::commit()');

        $pdo->beginTransaction();
        $pdo->exec('insert into "users" ("name") values ("john")');
        $pdo->commit();
    }

    #[Test]
    public function itShouldHandleIgnoreTransactionsMode(): void
    {
        $pdo = new PDOMock();
        $pdo->ignoreTransactions();

        $pdo->expectQuery('insert into "users" ("name") values ("john")');

        $pdo->beginTransaction();
        $pdo->exec('insert into "users" ("name") values ("john")');
        $pdo->commit();

        $pdo->assertExpectationsFulfilled();
    }
}
