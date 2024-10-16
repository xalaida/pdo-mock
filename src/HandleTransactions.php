<?php

namespace Xala\Elomock;

use Closure;
use Exception;
use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Override;
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
            throw new RuntimeException('Cannot expect PDO::beginTransaction() in ignore mode.');
        }

        $this->expectations[] = new Expectation('PDO::beginTransaction()');
    }

    public function expectCommit(): void
    {
        if ($this->ignoreTransactions) {
            throw new RuntimeException('Cannot expect PDO::commit() in ignore mode.');
        }

        $this->expectations[] = new Expectation('PDO::commit()');
    }

    public function expectRollback(): void
    {
        if ($this->ignoreTransactions) {
            throw new RuntimeException('Cannot expect PDO::rollback() in ignore mode.');
        }

        $this->expectations[] = new Expectation('PDO::rollback()');
    }

    public function expectTransaction(callable $callback): void
    {
        $this->expectBeginTransaction();

        $callback($this);

        $this->expectCommit();
    }

    public function assertBeganTransaction(): void
    {
        $this->assertQueried('PDO::beginTransaction()');
    }

    public function assertCommitted(): void
    {
        $this->assertQueried('PDO::commit()');
    }

    public function assertRolledBack(): void
    {
        $this->assertQueried('PDO::rollback()');
    }

    public function assertTransaction(Closure $callback)
    {
        $this->assertBeganTransaction();

        $callback($this);

        $this->assertCommitted();
    }

    #[Override]
    public function transaction(Closure $callback, $attempts = 1)
    {
        for ($currentAttempt = 1; $currentAttempt <= $attempts; $currentAttempt++) {
            $this->beginTransaction();

            try {
                $callbackResult = $callback($this);
            } catch (Throwable $e) {
                $this->verifyRollback();

                throw $e;
            }

            $levelBeingCommitted = $this->transactions;

            try {
                if ($this->transactions == 1) {
                    $this->fireConnectionEvent('committing');

                    $this->verifyCommit();
                }

                $this->transactions = max(0, $this->transactions - 1);
            } catch (Throwable $e) {
                $this->handleCommitTransactionException(
                    $e, $currentAttempt, $attempts
                );

                continue;
            }

            $this->transactionsManager?->commit(
                $this->getName(),
                $levelBeingCommitted,
                $this->transactions
            );

            $this->fireConnectionEvent('committed');

            return $callbackResult;
        }
    }

    #[Override]
    protected function createTransaction(): void
    {
        if ($this->transactions == 0) {
            $this->verifyBeginTransaction();
        } elseif ($this->transactions >= 1 && $this->queryGrammar->supportsSavepoints()) {
            $this->createSavepoint();
        }
    }

    protected function verifyBeginTransaction(): void
    {
        if ($this->ignoreTransactions) {
            return;
        }

        if ($this->deferWriteQueries) {
            $this->deferredQueries[] = [
                'query' => 'PDO::beginTransaction()',
                'bindings' => [],
            ];

            return;
        }

        TestCase::assertNotEmpty($this->expectations, 'Unexpected PDO::beginTransaction()');

        $expectation = array_shift($this->expectations);

        TestCase::assertEquals($expectation->query, 'PDO::beginTransaction()', 'Unexpected PDO::beginTransaction()');
    }

    #[Override]
    public function commit(): void
    {
        if ($this->transactions == 1) {
            $this->fireConnectionEvent('committing');

            $this->verifyCommit();
        }

        [$levelBeingCommitted, $this->transactions] = [
            $this->transactions,
            max(0, $this->transactions - 1),
        ];

        $this->transactionsManager?->commit(
            $this->getName(), $levelBeingCommitted, $this->transactions
        );

        $this->fireConnectionEvent('committed');
    }

    protected function verifyCommit(): void
    {
        if ($this->ignoreTransactions) {
            return;
        }

        if ($this->deferWriteQueries) {
            $this->deferredQueries[] = [
                'query' => 'PDO::commit()',
                'bindings' => [],
            ];

            return;
        }

        TestCase::assertNotEmpty($this->expectations, 'Unexpected PDO::commit()');

        $expectation = array_shift($this->expectations);

        TestCase::assertEquals($expectation->query, 'PDO::commit()', 'Unexpected PDO::commit()');
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
        if ($toLevel == 0) {
            $this->verifyRollback();
        } elseif ($this->queryGrammar->supportsSavepoints()) {
            $this->getPdo()->exec(
                $this->queryGrammar->compileSavepointRollBack('trans'.($toLevel + 1))
            );
        }
    }

    protected function verifyRollback(): void
    {
        if ($this->ignoreTransactions) {
            return;
        }

        if ($this->deferWriteQueries) {
            $this->deferredQueries[] = [
                'query' => 'PDO::rollback()',
                'bindings' => [],
            ];

            return;
        }

        TestCase::assertNotEmpty($this->expectations, 'Unexpected PDO::rollback()');

        $expectation = array_shift($this->expectations);

        TestCase::assertEquals($expectation->query, 'PDO::rollback()', 'Unexpected PDO::rollback()');
    }
}
