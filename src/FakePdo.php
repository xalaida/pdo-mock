<?php

namespace Xala\EloquentMock;

use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @mixin \PDO
 */
class FakePdo
{
    public FakeConnection $connection;

    public bool $inTransaction = false;

    public string | false $lastInsertId = false;

    public function __construct(
        public FakeLastInsertIdGenerator | null $lastInsertIdGenerator
    ) {
    }

    public function setConnection(FakeConnection $connection): void
    {
        $this->connection = $connection;
    }

    public function __call(string $name, array $arguments)
    {
        throw new RuntimeException("Unexpected PDO method call: {$name}");
    }

    public function expectBeginTransaction(): void
    {
        $this->connection->queryExpectations[] = new QueryExpectation('PDO::beginTransaction()');
    }

    public function expectCommit(): void
    {
        $this->connection->queryExpectations[] = new QueryExpectation('PDO::commit()');
    }

    public function expectRollback(): void
    {
        $this->connection->queryExpectations[] = new QueryExpectation('PDO::rollback()');
    }

    public function beginTransaction(): bool
    {
        $this->inTransaction = true;

        TestCase::assertNotEmpty($this->connection->queryExpectations, 'Unexpected PDO::beginTransaction()');

        $queryExpectation = array_shift($this->connection->queryExpectations);

        TestCase::assertEquals($queryExpectation->sql, 'PDO::beginTransaction()', 'Unexpected PDO::beginTransaction()');

        return true;
    }

    public function commit(): bool
    {
        $this->inTransaction = false;

        TestCase::assertNotEmpty($this->connection->queryExpectations, 'Unexpected PDO::commit()');

        $queryExpectation = array_shift($this->connection->queryExpectations);

        TestCase::assertEquals($queryExpectation->sql, 'PDO::commit()', 'Unexpected PDO::commit()');

        return true;
    }

    public function rollback(): bool
    {
        $this->inTransaction = false;

        TestCase::assertNotEmpty($this->connection->queryExpectations, 'Unexpected PDO::rollback()');

        $queryExpectation = array_shift($this->connection->queryExpectations);

        TestCase::assertEquals($queryExpectation->sql, 'PDO::rollback()', 'Unexpected PDO::rollback()');

        return true;
    }

    public function inTransaction(): bool
    {
        return $this->inTransaction;
    }

    public function lastInsertId(): string | false
    {
        if ($this->lastInsertId !== false) {
            $lastInsertId = $this->lastInsertId;

            $this->lastInsertId = false;

            return $lastInsertId;
        }

        if ($this->lastInsertIdGenerator) {
            return $this->lastInsertIdGenerator->generate();
        }

        return false;
    }
}
