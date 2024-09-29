<?php

namespace Xala\EloquentMock;

use Illuminate\Database\Connection;
use Override;
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

        if ($queryExpectation->sql === $query) {
            if ($this->compareBindings($queryExpectation->bindings, $bindings)) {
                return $queryExpectation->rows;
            }

            throw new RuntimeException(sprintf('Unexpected select query bindings: [%s] [%s]', $query, implode(', ', $bindings)));
        }

        throw new RuntimeException(sprintf('Unexpected select query: [%s]', $query));
    }

    protected function compareBindings(array $expectedBindings, array $actualBindings): bool
    {
        return $expectedBindings == $actualBindings;
    }
}
