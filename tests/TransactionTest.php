<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Xala\Elomock\FakePDO;

/**
 * @todo handle nested transactions
 */
class TransactionTest extends TestCase
{
    #[Test]
    public function itShouldVerifyTransaction(): void
    {
        $pdo = new FakePDO();

        $pdo->expectBeginTransaction();

        $pdo->expectQuery('insert into "users" ("name") values ("john")')
            ->affectRows(1);

        $pdo->expectCommit();

        static::assertFalse($pdo->inTransaction());

        $pdo->beginTransaction();

        $result = $pdo->exec('insert into "users" ("name") values ("john")');

        static::assertTrue($pdo->inTransaction());

        $pdo->commit();

        static::assertSame(1, $result);

        static::assertFalse($pdo->inTransaction());

        $pdo->assertExpectationsFulfilled();
    }

    #[Test]
    public function itShouldVerifyRollbackTransaction(): void
    {
        $pdo = new FakePDO();

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
        $pdo = new FakePDO();

        $pdo->expectBeginTransaction();

        $pdo->expectQuery('insert into "users" ("name") values ("john")');

        $this->expectException(ExpectationFailedException::class);

        $pdo->exec('insert into "users" ("name") values ("john")');
    }

    #[Test]
    public function itShouldVerifyTransactionUsingCallableSyntax(): void
    {
        $pdo = new FakePDO();

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
    public function itShouldFailWhenTransactionalQueryWasntExecuted(): void
    {
        $pdo = new FakePDO();

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
        $pdo = new FakePDO();
        $pdo->ignoreTransactions();

        $pdo->expectQuery('insert into "users" ("name") values ("john")');

        $pdo->beginTransaction();
        $pdo->exec('insert into "users" ("name") values ("john")');
        $pdo->commit();

        $pdo->assertExpectationsFulfilled();
    }
}
