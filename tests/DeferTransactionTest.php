<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;

class DeferTransactionTest extends TestCase
{
    #[Test]
    public function itShouldVerifyDeferredTransaction(): void
    {
        $connection = $this->getFakeConnection();

        $connection->deferWriteQueries();

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

        $connection->deferWriteQueries();

        $connection->transaction(function () use ($connection) {
            $connection
                ->table('users')
                ->insert(['name' => 'xala']);
        });

        $connection->assertTransactional(function () use ($connection) {
            $connection->assertQueried('insert into "users" ("name") values (?)', ['xala']);
        });
    }
}
