<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;

class SkipTransactionTest extends TestCase
{
    #[Test]
    public function itShouldVerifySkippedTransaction(): void
    {
        $connection = $this->getFakeConnection();

        $connection->skipWriteQueries();

        $connection->transaction(function () use ($connection) {
            $connection
                ->table('users')
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

        $connection->skipWriteQueries();

        $connection->transaction(function () use ($connection) {
            $connection
                ->table('users')
                ->insert(['name' => 'xala']);
        });

        $connection->assertTransaction(function () use ($connection) {
            $connection->assertQueried('insert into "users" ("name") values (?)', ['xala']);
        });
    }
}
