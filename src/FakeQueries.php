<?php

namespace Xala\EloquentMock;

use Closure;
use Illuminate\Database\Connection;
use Override;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @mixin Connection
 */
trait FakeQueries
{
    /**
     * @var array<int, QueryExpectation>
     */
    protected array $queryExpectations = [];

    protected array $queryExecuted = [];

    protected Closure | null $onInsertCallback = null;

    protected Closure | null $onUpdateCallback = null;

    protected Closure | null $onDeleteCallback = null;

    public function __construct()
    {
        // TODO: configure connection properly
        // TODO: support expending specific connection types (pgsql, mysql)
        parent::__construct(new FakePdo(), 'dbname', []);
    }

    public function shouldQuery(string $sql): QueryExpectation
    {
        $queryExpectation = new QueryExpectation($sql);

        $this->queryExpectations[] = $queryExpectation;

        return $queryExpectation;
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
    public function insert($query, $bindings = [])
    {
        $this->queryExecuted[] = [
            'sql' => $query,
            'bindings' => $bindings,
        ];

        if ($this->onInsertCallback) {
            return call_user_func($this->onInsertCallback, $query, $bindings);
        }

        $queryExpectation = array_shift($this->queryExpectations);

        if ($queryExpectation && $queryExpectation->sql === $query) {
            if ($this->compareBindings($queryExpectation->bindings, $bindings)) {
                $this->pdo->lastInsertId = $queryExpectation->lastInsertId;

                return $queryExpectation->successfulStatement;
            }

            throw new RuntimeException(sprintf('Unexpected insert query bindings: [%s] [%s]', $query, implode(', ', $bindings)));
        }

        throw new RuntimeException(sprintf('Unexpected insert query: [%s] [%s]', $query, implode(', ', $bindings)));
    }

    #[Override]
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

    protected function compareBindings(array | null $expectedBindings, array $actualBindings): bool
    {
        if (is_null($expectedBindings)) {
            return true;
        }

        return $expectedBindings == $actualBindings;
    }

    public function assertExpectedQueriesExecuted(): void
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
}
