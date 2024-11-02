<?php

namespace Tests\Xalaida\PDOMock;

use Xalaida\PDOMock\PDOMock;

class TransactionTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldCommitTransaction()
    {
        $pdo = new PDOMock();

        $pdo->expectBeginTransaction();

        $pdo->expect('insert into "books" ("title") values ("Kaidash’s Family")');

        $pdo->expectCommit();

        static::assertFalse($pdo->inTransaction());

        $pdo->beginTransaction();

        $pdo->exec('insert into "books" ("title") values ("Kaidash’s Family")');

        static::assertTrue($pdo->inTransaction());

        $pdo->commit();

        static::assertFalse($pdo->inTransaction());

        $pdo->assertExpectationsFulfilled();
    }

    /**
     * @test
     */
    public function itShouldRollbackTransaction()
    {
        $pdo = new PDOMock();
        $pdo->expectBeginTransaction();
        $pdo->expect('insert into "books" ("title") values ("Kaidash’s Family")');
        $pdo->expectRollback();

        $pdo->beginTransaction();
        $pdo->exec('insert into "books" ("title") values ("Kaidash’s Family")');
        $pdo->rollBack();

        $pdo->assertExpectationsFulfilled();
    }

    /**
     * @test
     */
    public function itShouldFailWhenQueryExecutedWithoutTransaction()
    {
        $pdo = new PDOMock();

        $pdo->expectBeginTransaction();

        $pdo->expect('insert into "books" ("title") values ("Kaidash’s Family")');

        $this->expectExceptionMessage('Unexpected query: insert into "books" ("title") values ("Kaidash’s Family")');

        $pdo->exec('insert into "books" ("title") values ("Kaidash’s Family")');
    }

    /**
     * @test
     */
    public function itShouldExpectTransactionUsingCallableSyntax()
    {
        $pdo = new PDOMock();
        $pdo->expectTransaction(function () use ($pdo) {
            $pdo->expect('insert into "books" ("title") values ("Kaidash’s Family")');
            $pdo->expect('insert into "books" ("title") values ("Shadows of the Forgotten Ancestors")');
        });

        $pdo->beginTransaction();
        $pdo->exec('insert into "books" ("title") values ("Kaidash’s Family")');
        $pdo->exec('insert into "books" ("title") values ("Shadows of the Forgotten Ancestors")');
        $pdo->commit();

        $pdo->assertExpectationsFulfilled();
    }

    /**
     * @test
     */
    public function itShouldFailWhenTransactionalQueryIsNotExecuted()
    {
        $pdo = new PDOMock();

        $pdo->expectTransaction(function () use ($pdo) {
            $pdo->expect('insert into "books" ("title") values ("Kaidash’s Family")');
            $pdo->expect('insert into "books" ("title") values ("Shadows of the Forgotten Ancestors")');
        });

        $pdo->beginTransaction();
        $pdo->exec('insert into "books" ("title") values ("Kaidash’s Family")');

        $this->expectExceptionMessage('Unexpected function: PDO::commit()');

        $pdo->commit();
    }

    /**
     * @test
     */
    public function itShouldIgnoreTransactionsWhenModeIsEnabled()
    {
        $pdo = new PDOMock();
        $pdo->ignoreTransactions();

        $pdo->expect('insert into "books" ("title") values ("Kaidash’s Family")');

        $pdo->beginTransaction();
        $pdo->exec('insert into "books" ("title") values ("Kaidash’s Family")');
        $pdo->commit();

        $pdo->assertExpectationsFulfilled();
    }
}
