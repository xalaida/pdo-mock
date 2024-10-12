<?php

namespace Xala\Elomock;

use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @extends \PDO
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

    public function beginTransaction(): bool
    {
        $this->connection->queryExecuted[] = [
            'sql' => 'PDO::beginTransaction()',
            'bindings' => [],
        ];

        $this->inTransaction = true;

        // TODO: refactor condition
        if (! $this->connection->ignoreTransactions && ! $this->connection->recordTransaction) {
            TestCase::assertNotEmpty($this->connection->queryExpectations, 'Unexpected PDO::beginTransaction()');

            $queryExpectation = array_shift($this->connection->queryExpectations);

            TestCase::assertEquals($queryExpectation->sql, 'PDO::beginTransaction()', 'Unexpected PDO::beginTransaction()');
        }

        return true;
    }

    public function commit(): bool
    {
        $this->connection->queryExecuted[] = [
            'sql' => 'PDO::commit()',
            'bindings' => [],
        ];

        $this->inTransaction = false;

        // TODO: refactor condition
        if (! $this->connection->ignoreTransactions && ! $this->connection->recordTransaction) {
            TestCase::assertNotEmpty($this->connection->queryExpectations, 'Unexpected PDO::commit()');

            $queryExpectation = array_shift($this->connection->queryExpectations);

            TestCase::assertEquals($queryExpectation->sql, 'PDO::commit()', 'Unexpected PDO::commit()');
        }

        return true;
    }

    public function rollback(): bool
    {
        $this->connection->queryExecuted[] = [
            'sql' => 'PDO::rollback()',
            'bindings' => [],
        ];

        $this->inTransaction = false;

        // TODO: refactor condition
        if (! $this->connection->ignoreTransactions && ! $this->connection->recordTransaction) {
            TestCase::assertNotEmpty($this->connection->queryExpectations, 'Unexpected PDO::rollback()');

            $queryExpectation = array_shift($this->connection->queryExpectations);

            TestCase::assertEquals($queryExpectation->sql, 'PDO::rollback()', 'Unexpected PDO::rollback()');
        }

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
