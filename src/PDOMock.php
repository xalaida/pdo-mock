<?php

namespace Xala\Elomock;

use Override;
use PDO;
use PDOException;
use PDOStatement;
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

    private array $errorInfo = ['', null, null];

    private string | null $errorCode = null;

    public function __construct(array $attributes = [])
    {
        $this->attributes = [
            $this::ATTR_ERRMODE => $this::ERRMODE_EXCEPTION,
            $this::ATTR_DEFAULT_FETCH_MODE => $this::FETCH_BOTH,
        ] + $attributes;
    }

    #[Override]
    public function setAttribute($attribute, $value): bool
    {
        $this->attributes[$attribute] = $value;

        return true;
    }

    #[Override]
    public function getAttribute($attribute): mixed
    {
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
        TestCase::assertNotEmpty($this->expectations, 'Unexpected query: ' . $statement);

        $expectation = array_shift($this->expectations);

        if (! is_null($expectation->prepared)) {
            TestCase::assertFalse($expectation->prepared, 'Statement is not prepared');
        }

        TestCase::assertSame($expectation->query, $statement, 'Unexpected query: ' . $statement);

        if ($expectation->exceptionOnExecute && $expectation->exceptionOnExecute->errorInfo) {
            $this->errorInfo = $expectation->exceptionOnExecute->errorInfo;
            $this->errorCode = $expectation->exceptionOnExecute->errorInfo[0];
        } else {
            $this->errorInfo = ['00000', null, null];
            $this->errorCode = $this->errorInfo[0];
        }

        if ($expectation->exceptionOnExecute) {
            if ($this->getAttribute($this::ATTR_ERRMODE) === $this::ERRMODE_SILENT) {
                return false;
            }

            if ($this->getAttribute($this::ATTR_ERRMODE) === $this::ERRMODE_WARNING) {
                trigger_error('PDO::exec(): ' . $expectation->exceptionOnExecute->getMessage(), E_USER_WARNING);

                return false;
            }

            if ($this->getAttribute($this::ATTR_ERRMODE) === $this::ERRMODE_EXCEPTION) {
                throw $expectation->exceptionOnExecute;
            }
        }

        if (! is_null($expectation->insertId)) {
            $this->lastInsertId = $expectation->insertId;
        }

        return count($expectation->rows) ?: $expectation->rowCount;
    }

    public function prepare($query, $options = []): PDOStatementMock | false
    {
        TestCase::assertNotEmpty($this->expectations, 'Unexpected query: ' . $query);

        $expectation = array_shift($this->expectations);

        if ($expectation->exceptionOnPrepare && $expectation->exceptionOnPrepare->errorInfo) {
            $this->errorInfo = $expectation->exceptionOnPrepare->errorInfo;
            $this->errorCode = $expectation->exceptionOnPrepare->errorInfo[0];
        } else {
            $this->errorInfo = ['00000', null, null];
            $this->errorCode = $this->errorInfo[0];
        }

        if ($expectation->exceptionOnPrepare) {
            if ($this->getAttribute($this::ATTR_ERRMODE) === $this::ERRMODE_SILENT) {
                return false;
            }

            if ($this->getAttribute($this::ATTR_ERRMODE) === $this::ERRMODE_WARNING) {
                trigger_error('PDO::prepare(): ' . $expectation->exceptionOnPrepare->getMessage(), E_USER_WARNING);

                return false;
            }

            if ($this->getAttribute($this::ATTR_ERRMODE) === $this::ERRMODE_EXCEPTION) {
                throw $expectation->exceptionOnPrepare;
            }
        }

        $statement = new PDOStatementMock($this, $expectation, $query);

        $statement->setFetchMode(
            $this->getAttribute($this::ATTR_DEFAULT_FETCH_MODE)
        );

        return $statement;
    }

    public function query($query, $fetchMode = null, ...$fetch_mode_args): PDOStatement
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
        if ($this->inTransaction) {
            throw new PDOException('There is already an active transaction');
        }

        $this->inTransaction = true;

        if ($this->ignoreTransactions) {
            return true;
        }

        // TODO: ensure transaction is expected
        $expectation = array_shift($this->expectations);

        TestCase::assertSame($expectation->query, 'PDO::beginTransaction()', 'Unexpected PDO::beginTransaction()');

        return true;
    }

    public function commit(): bool
    {
        if (! $this->inTransaction) {
            throw new PDOException('There is no active transaction');
        }

        $this->inTransaction = false;

        if ($this->ignoreTransactions) {
            return true;
        }

        // TODO: ensure transaction is expected
        $expectation = array_shift($this->expectations);

        TestCase::assertSame($expectation->query, 'PDO::commit()', 'Unexpected PDO::commit()');

        return true;
    }

    public function rollBack(): bool
    {
        if (! $this->inTransaction) {
            throw new PDOException('There is no active transaction');
        }

        $this->inTransaction = false;

        if ($this->ignoreTransactions) {
            return true;
        }

        // TODO: ensure transaction is expected
        $expectation = array_shift($this->expectations);

        TestCase::assertSame($expectation->query, 'PDO::rollback()', 'Unexpected PDO::rollback()');

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
