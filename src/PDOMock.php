<?php

namespace Xala\Elomock;

use Override;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PDOMock extends PDO
{
    /**
     * @var array<int, Expectation>
     */
    public array $expectations = [];

    public bool $ignoreTransactions = false;

    /**
     * @var array<int, int>
     */
    public array $attributes = [];

    protected bool $inTransaction = false;

    public string $lastInsertId = '0';

    // TODO: refactor this flag
    protected bool $executed = false;

    private array $errorInfo;

    private string | null $errorCode;

    /**
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct()
    {
        $this->attributes = [
            // TODO: define missing attributes
            self::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_BOTH,
        ];

        $this->errorInfo = ['', null, null];
        $this->errorCode = null;
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

    public function expect(string $query): Expectation
    {
        $expectation = new Expectation($query);

        $this->expectations[] = $expectation;

        return $expectation;
    }

    #[Override]
    public function exec($statement): int | false
    {
        $expectation = array_shift($this->expectations);

        if (! is_null($expectation->prepared)) {
            TestCase::assertFalse($expectation->prepared, 'Statement is not prepared');
        }

        TestCase::assertEquals($expectation->query, $statement);

        $this->errorInfo = ['00000', null, null];
        $this->errorCode = $this->errorInfo[0];

        if ($expectation->exceptionOnExecute) {
            // TODO: refactor
            if ($expectation->exceptionOnExecute->errorInfo) {
                $this->errorInfo = $expectation->exceptionOnExecute->errorInfo;
                $this->errorCode = $expectation->exceptionOnExecute->errorInfo[0];
            }

            throw $expectation->exceptionOnExecute;
        }

        if (! is_null($expectation->insertId)) {
            $this->lastInsertId = $expectation->insertId;
        }

        return $expectation->rowCount;
    }

    // TODO: handle $options
    public function prepare($query, $options = []): PDOStatementMock
    {
        TestCase::assertNotEmpty($this->expectations, 'Unexpected query: ' . $query);

        $expectation = array_shift($this->expectations);

        // TODO: refactor...
        $this->errorInfo = ['00000', null, null];
        $this->errorCode = $this->errorInfo[0];

        if ($expectation->exceptionOnPrepare) {
            // TODO: refactor
            if ($expectation->exceptionOnPrepare->errorInfo) {
                $this->errorInfo = $expectation->exceptionOnPrepare->errorInfo;
                $this->errorCode = $expectation->exceptionOnPrepare->errorInfo[0];
            }

            throw $expectation->exceptionOnPrepare;
        }

        $statement = new PDOStatementMock($this, $expectation, $query);

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

        $this->expectations[] = new Expectation('PDO::beginTransaction()');
    }

    public function expectCommit(): void
    {
        if ($this->ignoreTransactions) {
            throw new RuntimeException('Cannot expect PDO::commit() in ignore mode.');
        }

        $this->expectations[] = new Expectation('PDO::commit()');
    }

    public function expectRollback(): void
    {
        if ($this->ignoreTransactions) {
            throw new RuntimeException('Cannot expect PDO::rollback() in ignore mode.');
        }

        $this->expectations[] = new Expectation('PDO::rollback()');
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

        $expectation = array_shift($this->expectations);

        TestCase::assertEquals($expectation->query, 'PDO::beginTransaction()', 'Unexpected PDO::beginTransaction()');

        return true;
    }

    public function commit(): bool
    {
        $this->inTransaction = false;

        if ($this->ignoreTransactions) {
            return true;
        }

        $expectation = array_shift($this->expectations);

        TestCase::assertEquals($expectation->query, 'PDO::commit()', 'Unexpected PDO::commit()');

        return true;
    }

    public function rollBack(): bool
    {
        $this->inTransaction = false;

        if ($this->ignoreTransactions) {
            return true;
        }

        $expectation = array_shift($this->expectations);

        TestCase::assertEquals($expectation->query, 'PDO::rollback()', 'Unexpected PDO::rollback()');

        return true;
    }

    public function inTransaction(): bool
    {
        return $this->inTransaction;
    }

    public function lastInsertId($name = null): string
    {
        return $this->lastInsertId;
    }

    public function errorCode(): ?string
    {
        return $this->errorCode;
    }

    public function errorInfo(): array
    {
        return $this->errorInfo;
    }

    public function assertExpectationsFulfilled(): void
    {
        TestCase::assertEmpty($this->expectations, 'Some expectations were not fulfilled.');
    }
}
