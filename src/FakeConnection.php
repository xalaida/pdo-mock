<?php

namespace Xala\EloquentMock;

use Illuminate\Database\Connection;

class FakeConnection extends Connection
{
    protected array $queryExpectations = [];

    public function __construct()
    {
        // TODO: configure connection properly
        // TODO: support expending specific connection types (pgsql, mysql)
        parent::__construct(new FakePdo(), 'dbname', []);
    }

    public function shouldPrepare(string $query): QueryExpectation
    {
        $queryExpectation = new QueryExpectation($query);

        $this->queryExpectations[] = $queryExpectation;

        return $queryExpectation;
    }

    public function select($query, $bindings = [], $useReadPdo = true)
    {
        foreach ($this->queryExpectations as $queryExpectation) {
            if ($queryExpectation->sql === $query) {
                return $queryExpectation->rows;
            }
        }

        throw new \RuntimeException(sprintf('Unexpected select query: [%s]', $query));
    }
}
