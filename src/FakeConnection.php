<?php

namespace Xala\EloquentMock;

use Illuminate\Database\Connection;
use Override;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class FakeConnection extends Connection
{
    /**
     * @var array<int, QueryExpectation>
     */
    protected array $queryExpectations = [];

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
        $queryExpectation = array_shift($this->queryExpectations);

        if ($queryExpectation && $queryExpectation->sql === $query) {
            if ($this->compareBindings($queryExpectation->bindings, $bindings)) {
                return $queryExpectation->successfulStatement;
            }

            throw new RuntimeException(sprintf('Unexpected insert query bindings: [%s] [%s]', $query, implode(', ', $bindings)));
        }

        throw new RuntimeException(sprintf('Unexpected insert query: [%s] [%s]', $query, implode(', ', $bindings)));
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
        TestCase::assertEmpty(
            $this->queryExpectations, vsprintf("Some queries were not executed: %d", [
                count($this->queryExpectations),
            ])
        );
    }
}
