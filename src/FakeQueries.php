<?php

namespace Xala\Elomock;

use Closure;
use Exception;
use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Override;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

/**
 * @mixin Connection
 */
trait FakeQueries
{
    /**
     * @var array<int, Expectation>
     */
    public array $expectations = [];

    public array $writeQueriesForAssertions = [];

    public bool $ignoreTransactions = false;

    public bool $skipWriteQueries = false;

    public int | string | null $lastInsertId = null;

    public function expectQuery(string $sql, ?array $bindings = null): Expectation
    {
        $expectations = new Expectation($sql, $bindings);

        $this->expectations[] = $expectations;

        return $expectations;
    }

    public function shouldBeginTransaction(): void
    {
        if ($this->ignoreTransactions) {
            throw new RuntimeException('Cannot expect PDO::beginTransaction() in ignore mode.');
        }

        $this->expectations[] = new Expectation('PDO::beginTransaction()');
    }

    public function shouldCommit(): void
    {
        if ($this->ignoreTransactions) {
            throw new RuntimeException('Cannot expect PDO::commit() in ignore mode.');
        }

        $this->expectations[] = new Expectation('PDO::commit()');
    }

    public function shouldRollback(): void
    {
        if ($this->ignoreTransactions) {
            throw new RuntimeException('Cannot expect PDO::rollback() in ignore mode.');
        }

        $this->expectations[] = new Expectation('PDO::rollback()');
    }

    public function ignoreTransactions(bool $ignoreTransactions = true): void
    {
        $this->ignoreTransactions = $ignoreTransactions;
    }

    public function skipWriteQueries(bool $skipWriteQueries = true): void
    {
        $this->skipWriteQueries = $skipWriteQueries;
    }

    public function expectTransaction(callable $callback): void
    {
        $this->shouldBeginTransaction();

        $callback($this);

        $this->shouldCommit();
    }

    #[Override]
    // TODO: rewrite
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        $expectations = array_shift($this->expectations);

        if ($expectations && $expectations->query === $query) {
            if ($this->compareBindings($expectations->bindings, $bindings)) {
                return $expectations->rows;
            }

            throw new RuntimeException(sprintf('Unexpected select query bindings: [%s] [%s]', $query, implode(', ', $bindings)));
        }

        throw new RuntimeException(sprintf('Unexpected select query: [%s] [%s]', $query, implode(', ', $bindings)));
    }

    #[Override]
    public function statement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return true;
            }

            if ($this->skipWriteQueries) {
                $this->writeQueriesForAssertions[] = [
                    'sql' => $query,
                    'bindings' => $bindings,
                ];

                return true;
            }

            TestCase::assertNotEmpty($this->expectations, sprintf('Unexpected query: [%s] [%s]', $query, implode(', ', $bindings)));

            $expectations = array_shift($this->expectations);

            TestCase::assertEquals($expectations->query, $query, sprintf('Unexpected query: [%s] [%s]', $query, implode(', ', $bindings)));

            if (! is_null($expectations->bindings)) {
                TestCase::assertEquals($expectations->bindings, $bindings, sprintf("Unexpected query bindings: [%s] [%s]", $query, implode(', ', $bindings)));
            }

            $this->lastInsertId = $expectations->lastInsertId;

            if ($expectations->exception) {
                throw $expectations->exception;
            }

            $this->recordsHaveBeenModified();

            return $expectations->successfulStatement;
        });
    }

    #[Override]
    public function affectingStatement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return 0;
            }

            if ($this->skipWriteQueries) {
                $this->writeQueriesForAssertions[] = [
                    'sql' => $query,
                    'bindings' => $bindings,
                ];

                return 1;
            }

            TestCase::assertNotEmpty($this->expectations, sprintf('Unexpected query: [%s] [%s]', $query, implode(', ', $bindings)));

            $expectations = array_shift($this->expectations);

            TestCase::assertEquals($expectations->query, $query, sprintf('Unexpected query: [%s] [%s]', $query, implode(', ', $bindings)));

            if (! is_null($expectations->bindings)) {
                TestCase::assertEquals($expectations->bindings, $bindings, sprintf("Unexpected query bindings: [%s] [%s]", $query, implode(', ', $bindings)));
            }

            if ($expectations->exception) {
                throw $expectations->exception;
            }

            $this->recordsHaveBeenModified(
                $expectations->affectedRows > 0
            );

            return $expectations->affectedRows;
        });
    }

    public function getLastInsertId()
    {
        if ($this->lastInsertId) {
            $lastInsertId = $this->lastInsertId;

            $this->lastInsertId = null;

            return $lastInsertId;
        }

        return $this->insertIdGenerator->generate();
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

        if ($this->skipWriteQueries) {
            $this->writeQueriesForAssertions[] = [
                'sql' => 'PDO::beginTransaction()',
                'bindings' => [],
            ];

            return;
        }

        TestCase::assertNotEmpty($this->expectations, 'Unexpected PDO::beginTransaction()');

        $expectations = array_shift($this->expectations);

        TestCase::assertEquals($expectations->query, 'PDO::beginTransaction()', 'Unexpected PDO::beginTransaction()');
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

        if ($this->skipWriteQueries) {
            $this->writeQueriesForAssertions[] = [
                'sql' => 'PDO::commit()',
                'bindings' => [],
            ];

            return;
        }

        TestCase::assertNotEmpty($this->expectations, 'Unexpected PDO::commit()');

        $expectations = array_shift($this->expectations);

        TestCase::assertEquals($expectations->query, 'PDO::commit()', 'Unexpected PDO::commit()');
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

        if ($this->skipWriteQueries) {
            $this->writeQueriesForAssertions[] = [
                'sql' => 'PDO::rollback()',
                'bindings' => [],
            ];

            return;
        }

        TestCase::assertNotEmpty($this->expectations, 'Unexpected PDO::rollback()');

        $expectations = array_shift($this->expectations);

        TestCase::assertEquals($expectations->query, 'PDO::rollback()', 'Unexpected PDO::rollback()');
    }

    #[Override]
    protected function runQueryCallback($query, $bindings, Closure $callback)
    {
        try {
            return $callback($query, $bindings);
        } catch (Exception $e) {
            // Rethrow PHPUnit assertion exception
            if ($e instanceof ExpectationFailedException) {
                throw $e;
            }

            throw new QueryException(
                $this->getName(), $query, $this->prepareBindings($bindings), $e
            );
        }
    }

    protected function compareBindings(array | null $expectedBindings, array $actualBindings): bool
    {
        if (is_null($expectedBindings)) {
            return true;
        }

        return $expectedBindings == $actualBindings;
    }

    protected function isUniqueConstraintError(Exception $exception): bool
    {
        return $exception->getMessage() === 'Unique constraint error';
    }

    public function assertExpectationsFulfilled(): void
    {
        TestCase::assertEmpty($this->expectations, 'Some expectations were not fulfilled.');
    }

    public function assertWriteQueriesFulfilled(): void
    {
        TestCase::assertEmpty($this->writeQueriesForAssertions, 'Some write queries were not fulfilled.');
    }

    public function assertQueried(string $sql, array | null $bindings = []): void
    {
        TestCase::assertNotEmpty($this->writeQueriesForAssertions, 'No queries were executed');

        $writeQueriesForAssertions = array_shift($this->writeQueriesForAssertions);

        TestCase::assertEquals($sql, $writeQueriesForAssertions['sql'], 'Query does not match');
        TestCase::assertEquals($bindings, $writeQueriesForAssertions['bindings'], 'Bindings do not match');
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
}
