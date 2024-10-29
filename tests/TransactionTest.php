<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\ExpectationFailedException;
use Xala\Elomock\PDOMock;

class TransactionTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldCommitTransaction(): void
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
    public function itShouldRollbackTransaction(): void
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
    public function itShouldFailWhenQueryExecutedWithoutTransaction(): void
    {
        $pdo = new PDOMock();

        $pdo->expectBeginTransaction();

        $pdo->expect('insert into "books" ("title") values ("Kaidash’s Family")');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query: insert into "books" ("title") values ("Kaidash’s Family")');

        $pdo->exec('insert into "books" ("title") values ("Kaidash’s Family")');
    }

    /**
     * @test
     */
    public function itShouldExpectTransactionUsingCallableSyntax(): void
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
    public function itShouldFailWhenTransactionalQueryIsNotExecuted(): void
    {
        $pdo = new PDOMock();

        $pdo->expectTransaction(function () use ($pdo) {
            $pdo->expect('insert into "books" ("title") values ("Kaidash’s Family")');
            $pdo->expect('insert into "books" ("title") values ("Shadows of the Forgotten Ancestors")');
        });

        $pdo->beginTransaction();
        $pdo->exec('insert into "books" ("title") values ("Kaidash’s Family")');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected PDO::commit()');

        $pdo->commit();
    }

    /**
     * @test
     */
    public function itShouldIgnoreTransactionsWhenModeIsEnabled(): void
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
