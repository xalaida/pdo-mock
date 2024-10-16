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

class FakeConnection extends Connection
{
    public array $expectations = [];

    public array $writeQueriesForAssertions = [];

    public bool $ignoreTransactions = false;

    public bool $skipWriteQueries = false;

    public int | string | null $lastInsertId = null;

    public InsertIdGenerator $insertIdGenerator;

    public function __construct()
    {
        parent::__construct(null);

        $this->insertIdGenerator = new InsertIdGenerator();
    }

    public function ignoreTransactions(bool $ignoreTransactions = true): void
    {
        $this->ignoreTransactions = $ignoreTransactions;
    }

    public function skipWriteQueries(bool $skipWriteQueries = true): void
    {
        $this->skipWriteQueries = $skipWriteQueries;
    }

    public function getLastInsertId(): int | string | null
    {
        if (! is_null($this->lastInsertId)) {
            $lastInsertId = $this->lastInsertId;

            $this->lastInsertId = null;

            return $lastInsertId;
        }

        return $this->insertIdGenerator->generate();
    }

    public function expectQuery(string $query, array | Closure | null $bindings = null): Expectation
    {
        $expectation = new Expectation($query, $bindings);

        $this->expectations[] = $expectation;

        return $expectation;
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

    #[Override]
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return [];
            }

            $expectation = $this->verifyQueryExpectation($query, $bindings);

            // TODO: add ability to throw

            return $expectation->rows;
        });
    }

    public function cursor($query, $bindings = [], $useReadPdo = true)
    {
        $rows = $this->select($query, $bindings, $useReadPdo);

        foreach ($rows as $row) {
            yield $row;
        }
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
                    'query' => $query,
                    'bindings' => $bindings,
                ];

                return true;
            }

            $expectation = $this->verifyQueryExpectation($query, $bindings);

            $this->lastInsertId = $expectation->lastInsertId;

            if ($expectation->exception) {
                throw $expectation->exception;
            }

            $this->recordsHaveBeenModified();

            return $expectation->successfulStatement;
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
                    'query' => $query,
                    'bindings' => $bindings,
                ];

                return 1;
            }

            $expectation = $this->verifyQueryExpectation($query, $bindings);

            if ($expectation->exception) {
                throw $expectation->exception;
            }

            $this->recordsHaveBeenModified(
                $expectation->affectedRows > 0
            );

            return $expectation->affectedRows;
        });
    }

    #[Override]
    public function unprepared($query)
    {
        return $this->run($query, [], function ($query) {
            if ($this->pretending()) {
                return true;
            }

            if ($this->skipWriteQueries) {
                $this->writeQueriesForAssertions[] = [
                    'query' => $query,
                    'bindings' => [],
                ];

                return 1;
            }

            TestCase::assertNotEmpty($this->expectations, sprintf('Unexpected query: [%s]', $query));

            $expectation = array_shift($this->expectations);

            TestCase::assertEquals($expectation->query, $query, sprintf('Unexpected query: [%s]', $query));

            if ($expectation->exception) {
                throw $expectation->exception;
            }

            $this->recordsHaveBeenModified(
                $expectation->affectedRows > 0
            );

            return $expectation->affectedRows;
        });
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

        if ($this->skipWriteQueries) {
            $this->writeQueriesForAssertions[] = [
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

        if ($this->skipWriteQueries) {
            $this->writeQueriesForAssertions[] = [
                'query' => 'PDO::rollback()',
                'bindings' => [],
            ];

            return;
        }

        TestCase::assertNotEmpty($this->expectations, 'Unexpected PDO::rollback()');

        $expectation = array_shift($this->expectations);

        TestCase::assertEquals($expectation->query, 'PDO::rollback()', 'Unexpected PDO::rollback()');
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

    // TODO: check if it works with latest laravel version
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
        $queriesFormatted = implode(PHP_EOL, array_map(function (array $query) {
            return sprintf('%s [%s]', $query['query'], implode(', ', $query['bindings']));
        }, $this->writeQueriesForAssertions));

        TestCase::assertEmpty($this->writeQueriesForAssertions, 'Some write queries were not fulfilled:' . PHP_EOL . $queriesFormatted);
    }

    public function assertQueried(string $query, array | Closure | null $bindings = null): void
    {
        TestCase::assertNotEmpty($this->writeQueriesForAssertions, 'No queries were executed');

        $writeQueriesForAssertions = array_shift($this->writeQueriesForAssertions);

        TestCase::assertEquals($query, $writeQueriesForAssertions['query'], 'Query does not match');

        $this->validateBindings($bindings, $writeQueriesForAssertions['bindings'], $writeQueriesForAssertions['query']);
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

    protected function verifyQueryExpectation(string $query, array $bindings): Expectation
    {
        $bindings = $this->prepareBindings($bindings);

        TestCase::assertNotEmpty($this->expectations, sprintf('Unexpected query: [%s] [%s]', $query, implode(', ', $bindings)));

        $expectation = array_shift($this->expectations);

        TestCase::assertEquals($expectation->query, $query, sprintf('Unexpected query: [%s] [%s]', $query, implode(', ', $bindings)));

        $this->validateBindings($expectation->bindings, $bindings, $query);

        return $expectation;
    }

    protected function validateBindings(array | Closure | null $expectedBindings, array $bindings, string $query): void
    {
        if (is_null($expectedBindings)) {
            return;
        }

        if (is_array($expectedBindings)) {
            TestCase::assertEquals($expectedBindings, $bindings, sprintf('Unexpected query bindings: [%s] [%s]', $query, implode(', ', $bindings)));
        }

        if (is_callable($expectedBindings)) {
            TestCase::assertNotFalse(call_user_func($expectedBindings, $bindings), sprintf('Unexpected query bindings: [%s] [%s]', $query, implode(', ', $bindings)));
        }
    }

    #[Override]
    protected function getDefaultPostProcessor(): FakeProcessor
    {
        return new FakeProcessor();
    }

    #[Override]
    public function reconnectIfMissingConnection()
    {
        //
    }
}
