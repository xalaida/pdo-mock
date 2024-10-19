<?php

namespace Xala\Elomock;

use Closure;
use Override;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

/**
 * @mixin FakeConnection
 */
trait HandleTransactions
{
    public bool $ignoreTransactions = false;

    public function ignoreTransactions(bool $ignoreTransactions = true): void
    {
        $this->ignoreTransactions = $ignoreTransactions;
    }

    public function expectBeginTransaction(): void
    {
        if ($this->ignoreTransactions) {
            throw new RuntimeException('Cannot expect DB::beginTransaction() in ignore mode.');
        }

        $this->expectations[] = new Expectation('DB::beginTransaction()');
    }

    public function expectCommit(): void
    {
        if ($this->ignoreTransactions) {
            throw new RuntimeException('Cannot expect DB::commit() in ignore mode.');
        }

        $this->expectations[] = new Expectation('DB::commit()');
    }

    public function expectRollback(): void
    {
        if ($this->ignoreTransactions) {
            throw new RuntimeException('Cannot expect DB::rollback() in ignore mode.');
        }

        $this->expectations[] = new Expectation('DB::rollback()');
    }

    public function expectTransaction(callable $callback): void
    {
        $this->expectBeginTransaction();

        $callback($this);

        $this->expectCommit();
    }

    public function assertBeganTransaction(): void
    {
        $this->assertQueried('DB::beginTransaction()');
    }

    public function assertCommitted(): void
    {
        $this->assertQueried('DB::commit()');
    }

    public function assertRolledBack(): void
    {
        $this->assertQueried('DB::rollback()');
    }

    public function assertTransactional(Closure $callback)
    {
        $this->assertBeganTransaction();

        $callback($this);

        $this->assertCommitted();
    }

    #[Override]
    public function transaction(Closure $callback, $attempts = 1)
    {
        $this->beginTransaction();

        try {
            $callbackResult = $callback($this);
        } catch (Throwable $e) {
            if (! ($e instanceof ExpectationFailedException)) {
                $this->verifyRollback();
            }

            throw $e;
        }

        try {
            if ($this->transactions == 1) {
                $this->fireConnectionEvent('committing');

                $this->verifyCommit();
            }
        } finally {
            $this->transactions = max(0, $this->transactions - 1);
        }

        if ($this->afterCommitCallbacksShouldBeExecuted()) {
            $this->transactionsManager?->commit($this->getName());
        }

        $this->fireConnectionEvent('committed');

        return $callbackResult;
    }

    public function beginTransaction(): void
    {
        $this->createTransaction();

        $this->transactions++;

        $this->transactionsManager?->begin(
            $this->getName(), $this->transactions
        );

        $this->fireConnectionEvent('beganTransaction');
    }

    protected function afterCommitCallbacksShouldBeExecuted(): bool
    {
        if ($this->transactions == 0) {
            return true;
        }

        if (is_null($this->transactionsManager)) {
            return false;
        }

        return $this->transactionsManager->callbackApplicableTransactions()->count() === 1;
    }

    #[Override]
    protected function createTransaction(): void
    {
        $this->verifyBeginTransaction();
    }

    protected function verifyBeginTransaction(): void
    {
        if ($this->ignoreTransactions) {
            return;
        }

        if ($this->deferWriteQueries) {
            $this->deferredQueries[] = [
                'query' => 'DB::beginTransaction()',
                'bindings' => [],
            ];

            return;
        }

        TestCase::assertNotEmpty($this->expectations, 'Unexpected DB::beginTransaction()');

        $expectation = array_shift($this->expectations);

        TestCase::assertEquals($expectation->query, 'DB::beginTransaction()', 'Unexpected DB::beginTransaction()');
    }

    #[Override]
    public function commit(): void
    {
        if ($this->transactions == 1) {
            $this->fireConnectionEvent('committing');

            $this->verifyCommit();
        }

        $this->transactions = max(0, $this->transactions - 1);

        if ($this->afterCommitCallbacksShouldBeExecuted()) {
            $this->transactionsManager?->commit($this->getName());
        }

        $this->fireConnectionEvent('committed');
    }

    protected function verifyCommit(): void
    {
        if ($this->ignoreTransactions) {
            return;
        }

        if ($this->deferWriteQueries) {
            $this->deferredQueries[] = [
                'query' => 'DB::commit()',
                'bindings' => [],
            ];

            return;
        }

        TestCase::assertNotEmpty($this->expectations, 'Unexpected DB::commit()');

        $expectation = array_shift($this->expectations);

        TestCase::assertEquals($expectation->query, 'DB::commit()', 'Unexpected DB::commit()');
    }

    #[Override]
    public function rollBack($toLevel = null)
    {
        $toLevel = is_null($toLevel)
            ? $this->transactions - 1
            : $toLevel;

        if ($toLevel < 0 || $toLevel >= $this->transactions) {
            return;
        }

        $this->performRollBack($toLevel);

        $this->transactions = $toLevel;

        $this->transactionsManager?->rollback(
            $this->getName(), $this->transactions
        );

        $this->fireConnectionEvent('rollingBack');
    }

    #[Override]
    protected function performRollBack($toLevel)
    {
        $this->verifyRollback();
    }

    protected function verifyRollback(): void
    {
        if ($this->ignoreTransactions) {
            return;
        }

        if ($this->deferWriteQueries) {
            $this->deferredQueries[] = [
                'query' => 'DB::rollback()',
                'bindings' => [],
            ];

            return;
        }

        TestCase::assertNotEmpty($this->expectations, 'Unexpected DB::rollback()');

        $expectation = array_shift($this->expectations);

        TestCase::assertEquals($expectation->query, 'DB::rollback()', 'Unexpected DB::rollback()');
    }
}
