<?php

namespace Tests\Xala\EloquentMock\Support;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\ConnectionResolverInterface;

class FakeConnectionResolver implements ConnectionResolverInterface
{
    public function __construct(
        public ConnectionInterface $connection
    ) {
    }

    public function connection($name = null): ConnectionInterface
    {
        return $this->connection;
    }

    public function getDefaultConnection(): string
    {
        return 'fake';
    }

    public function setDefaultConnection($name)
    {
        //
    }
}
