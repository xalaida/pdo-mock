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

    public bool $ignoreTransactions = false;

    public bool $recordTransaction = false;

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
        if ($this->ignoreTransactions) {
            throw new RuntimeException('Cannot expect PDO::beginTransaction() in ignore mode.');
        }

        $this->connection->queryExpectations[] = new QueryExpectation('PDO::beginTransaction()');
    }

    public function expectCommit(): void
    {
        if ($this->ignoreTransactions) {
            throw new RuntimeException('Cannot expect PDO::commit() in ignore mode.');
        }

        $this->connection->queryExpectations[] = new QueryExpectation('PDO::commit()');
    }

    public function expectRollback(): void
    {
        if ($this->ignoreTransactions) {
            throw new RuntimeException('Cannot expect PDO::rollback() in ignore mode.');
        }

        $this->connection->queryExpectations[] = new QueryExpectation('PDO::rollback()');
    }

    public function ignoreTransactions(): void
    {
        $this->ignoreTransactions = true;
    }

    public function handleTransactions(): void
    {
        $this->ignoreTransactions = false;
    }

    public function recordTransactions(): void
    {
        $this->recordTransaction = true;
    }

    public function beginTransaction(): bool
    {
        $this->connection->queryExecuted[] = [
            'sql' => 'PDO::beginTransaction()',
            'bindings' => [],
        ];

        $this->inTransaction = true;

        // TODO: refactor condition
        if (! $this->ignoreTransactions && ! $this->recordTransaction) {
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
        if (! $this->ignoreTransactions && ! $this->recordTransaction) {
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
        if (! $this->ignoreTransactions && ! $this->recordTransaction) {
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
