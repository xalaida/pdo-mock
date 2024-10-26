<?php

namespace Xala\Elomock;

use Override;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PDOMock extends PDO
{
    /**
     * @var array<int, QueryExpectation>
     */
    public array $expectations = [];

    public bool $ignoreTransactions = false;

    /**
     * @var array<int, int>
     */
    public array $attributes = [];

    protected bool $inTransaction = false;

    public string $lastInsertId = '0';

    /**
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct()
    {
        $this->attributes = [
            // TODO: define missing attributes
            self::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_BOTH,
        ];
    }

    #[Override]
    public function setAttribute($attribute, $value)
    {
        $this->attributes[$attribute] = $value;

        return true;
    }

    #[Override]
    public function getAttribute($attribute)
    {
        // TODO: handle unknown attributes

        return $this->attributes[$attribute];
    }

    public function ignoreTransactions(bool $ignoreTransactions = true): void
    {
        $this->ignoreTransactions = $ignoreTransactions;
    }

    public function expect(string $query): QueryExpectation
    {
        $expectation = new QueryExpectation($query);

        $this->expectations[] = $expectation;

        return $expectation;
    }

    #[Override]
    public function exec($statement)
    {
        // TODO: ensure there is expectation defined (not empty)
        $expectation = array_shift($this->expectations);

        if (! is_null($expectation->prepared)) {
            TestCase::assertFalse($expectation->prepared, 'Statement is not prepared');
        }

        TestCase::assertEquals($expectation->query, $statement);

        if ($expectation->exception) {
            throw $expectation->exception;
        }

        if (! is_null($expectation->insertId)) {
            $this->lastInsertId = $expectation->insertId;
        }

        return $expectation->rowCount;
    }

    // TODO: handle $options
    public function prepare($query, $options = [])
    {
        $statement = new PDOStatement($this, $query);

        $statement->setFetchMode($this->getAttribute($this::ATTR_DEFAULT_FETCH_MODE));

        return $statement;
    }

    // TODO: handle other arguments
    public function query($query, $fetchMode = null, ...$fetch_mode_args)
    {
        $statement = $this->prepare($query);

        $statement->execute();

        return $statement;
    }

    public function expectBeginTransaction(): void
    {
        if ($this->ignoreTransactions) {
            throw new RuntimeException('Cannot expect PDO::beginTransaction() in ignore mode.');
        }

        $this->expectations[] = new QueryExpectation('PDO::beginTransaction()');
    }

    public function expectCommit(): void
    {
        if ($this->ignoreTransactions) {
            throw new RuntimeException('Cannot expect PDO::commit() in ignore mode.');
        }

        $this->expectations[] = new QueryExpectation('PDO::commit()');
    }

    public function expectRollback(): void
    {
        if ($this->ignoreTransactions) {
            throw new RuntimeException('Cannot expect PDO::rollback() in ignore mode.');
        }

        $this->expectations[] = new QueryExpectation('PDO::rollback()');
    }

    public function expectTransaction(callable $callback): void
    {
        $this->expectBeginTransaction();

        $callback($this);

        $this->expectCommit();
    }

    public function beginTransaction(): bool
    {
        $this->inTransaction = true;

        if ($this->ignoreTransactions) {
            return true;
        }

        // TODO: ensure there is expectation defined (not empty)
        $expectation = array_shift($this->expectations);

        // TODO: use proper assertions
        TestCase::assertEquals($expectation->query, 'PDO::beginTransaction()', 'Unexpected PDO::beginTransaction()');

        return true;
    }

    public function commit(): bool
    {
        $this->inTransaction = false;

        if ($this->ignoreTransactions) {
            return true;
        }

        // TODO: ensure there is expectation defined (not empty)
        $expectation = array_shift($this->expectations);

        // TODO: use proper assertions
        TestCase::assertEquals($expectation->query, 'PDO::commit()', 'Unexpected PDO::commit()');

        return true;
    }

    public function rollBack(): bool
    {
        $this->inTransaction = false;

        if ($this->ignoreTransactions) {
            return true;
        }

        // TODO: ensure there is expectation defined (not empty)
        $expectation = array_shift($this->expectations);

        // TODO: use proper assertions
        TestCase::assertEquals($expectation->query, 'PDO::rollback()', 'Unexpected PDO::rollback()');

        return true;
    }

    public function inTransaction(): bool
    {
        return $this->inTransaction;
    }

    // TODO: support $name
    public function lastInsertId($name = null)
    {
        return $this->lastInsertId;
    }

    public function assertExpectationsFulfilled(): void
    {
        // TODO: improve error message
        TestCase::assertEmpty($this->expectations, 'Some expectations were not fulfilled.');
    }
}
