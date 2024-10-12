<?php

namespace Xala\EloquentMock;

use Closure;
use Exception;
use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Override;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @property FakePdo pdo
 * @mixin Connection
 */
trait FakeQueries
{
    /**
     * @var array<int, QueryExpectation>
     */
    public array $queryExpectations = [];

    public array $queryExecuted = [];

    protected Closure | null $onInsertCallback = null;

    protected Closure | null $onUpdateCallback = null;

    protected Closure | null $onDeleteCallback = null;

    public function shouldQuery(string $sql): QueryExpectation
    {
        $queryExpectation = new QueryExpectation($sql);

        $this->queryExpectations[] = $queryExpectation;

        return $queryExpectation;
    }

    public function shouldBeginTransaction(): void
    {
        $this->pdo->expectBeginTransaction();
    }

    public function shouldCommit(): void
    {
        $this->pdo->expectCommit();
    }

    public function shouldRollback(): void
    {
        $this->pdo->expectRollback();
    }

    public function ignoreTransactions(): void
    {
        $this->pdo->ignoreTransactions();
    }

    public function recordTransactions(): void
    {
        $this->pdo->recordTransactions();
    }

    public function expectTransaction(callable $callback): void
    {
        $this->shouldBeginTransaction();

        $callback($this);

        $this->shouldCommit();
    }

    public function onInsertQuery(Closure $callback): static
    {
        $this->onInsertCallback = $callback;

        return $this;
    }

    public function onUpdateQuery(Closure $callback): static
    {
        $this->onUpdateCallback = $callback;

        return $this;
    }

    public function onDeleteQuery(Closure $callback): static
    {
        $this->onDeleteCallback = $callback;

        return $this;
    }

    #[Override]
    // TODO: rewrite
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        $queryExpectation = array_shift($this->queryExpectations);

        if ($queryExpectation && $queryExpectation->sql === $query) {
            if ($this->compareBindings($queryExpectation->bindings, $bindings)) {
                return $queryExpectation->rows;
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

            // TODO: use built-in query recorder
            $this->queryExecuted[] = [
                'sql' => $query,
                'bindings' => $bindings,
            ];

            // TODO: do not use callbacks for write, simplify this
             if ($this->onInsertCallback) {
                 return call_user_func($this->onInsertCallback, $query, $bindings);
             }

            TestCase::assertNotEmpty($this->queryExpectations, sprintf('Unexpected query: [%s] [%s]', $query, implode(', ', $bindings)));

            $queryExpectation = array_shift($this->queryExpectations);

            TestCase::assertEquals($queryExpectation->sql, $query, sprintf('Unexpected query: [%s] [%s]', $query, implode(', ', $bindings)));

            if (! is_null($queryExpectation->bindings)) {
                TestCase::assertEquals($queryExpectation->bindings, $bindings, sprintf("Unexpected query bindings: [%s] [%s]", $query, implode(', ', $bindings)));
            }

            $this->pdo->lastInsertId = $queryExpectation->lastInsertId;

            if ($queryExpectation->exception) {
                throw $queryExpectation->exception;
            }

            $this->recordsHaveBeenModified();

            return $queryExpectation->successfulStatement;
        });
    }

    #[Override]
    public function affectingStatement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return 0;
            }

            // TODO: use built-in query recorder
            $this->queryExecuted[] = [
                'sql' => $query,
                'bindings' => $bindings,
            ];

            // TODO: do not use callbacks for write
             if ($this->onInsertCallback) {
                 return call_user_func($this->onInsertCallback, $query, $bindings);
             }

            TestCase::assertNotEmpty($this->queryExpectations, sprintf('Unexpected query: [%s] [%s]', $query, implode(', ', $bindings)));

            $queryExpectation = array_shift($this->queryExpectations);

            TestCase::assertEquals($queryExpectation->sql, $query, sprintf('Unexpected query: [%s] [%s]', $query, implode(', ', $bindings)));

            if (! is_null($queryExpectation->bindings)) {
                TestCase::assertEquals($queryExpectation->bindings, $bindings, sprintf("Unexpected query bindings: [%s] [%s]", $query, implode(', ', $bindings)));
            }

            if ($queryExpectation->exception) {
                throw $queryExpectation->exception;
            }

            $this->recordsHaveBeenModified(
                $queryExpectation->affectedRows > 0
            );

            return $queryExpectation->affectedRows;
        });
    }

    #[Override]
    // TODO: use affectingStatement instead
    public function update($query, $bindings = [])
    {
        $this->queryExecuted[] = [
            'sql' => $query,
            'bindings' => $bindings,
        ];

        if ($this->onUpdateCallback) {
            return call_user_func($this->onUpdateCallback, $query, $bindings);
        }

        $queryExpectation = array_shift($this->queryExpectations);

        if ($queryExpectation && $queryExpectation->sql === $query) {
            if ($this->compareBindings($queryExpectation->bindings, $bindings)) {
                return $queryExpectation->affectedRows;
            }

            throw new RuntimeException(sprintf('Unexpected update query bindings: [%s] [%s]', $query, implode(', ', $bindings)));
        }

        throw new RuntimeException(sprintf('Unexpected update query: [%s] [%s]', $query, implode(', ', $bindings)));
    }

    #[Override]
    // TODO: use affectingStatement instead
    public function delete($query, $bindings = [])
    {
        $this->queryExecuted[] = [
            'sql' => $query,
            'bindings' => $bindings,
        ];

        if ($this->onDeleteCallback) {
            return call_user_func($this->onDeleteCallback, $query, $bindings);
        }

        $queryExpectation = array_shift($this->queryExpectations);

        if ($queryExpectation && $queryExpectation->sql === $query) {
            if ($this->compareBindings($queryExpectation->bindings, $bindings)) {
                return $queryExpectation->affectedRows;
            }

            throw new RuntimeException(sprintf('Unexpected delete query bindings: [%s] [%s]', $query, implode(', ', $bindings)));
        }

        throw new RuntimeException(sprintf('Unexpected delete query: [%s] [%s]', $query, implode(', ', $bindings)));
    }

    public function getLastInsertId()
    {
        return $this->pdo->lastInsertId;
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

            // Default behavior
            if ($this->isUniqueConstraintError($e)) {
                throw new UniqueConstraintViolationException(
                    $this->getName(), $query, $this->prepareBindings($bindings), $e
                );
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
        // TODO: format this to display all queries and bindings, each on new line
        TestCase::assertEmpty(
            $this->queryExpectations, vsprintf("Some queries were not executed: %d", [
                count($this->queryExpectations),
            ])
        );
    }

    public function assertQueried(string $sql, array | null $bindings = []): void
    {
        TestCase::assertNotEmpty($this->queryExecuted, 'No queries were executed');

        $queryExecuted = array_shift($this->queryExecuted);

        TestCase::assertEquals($sql, $queryExecuted['sql'], 'Query does not match');
        TestCase::assertEquals($bindings, $queryExecuted['bindings'], 'Bindings do not match');
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
