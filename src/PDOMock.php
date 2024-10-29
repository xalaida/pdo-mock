<?php

namespace Xala\Elomock;

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
    public $expectations = [];

    /**
     * @var bool
     */
    public $ignoreTransactions = false;

    /**
     * @var array<int, int>
     */
    public $attributes = [];

    /**
     * @var bool
     */
    protected $inTransaction = false;

    /**
     * @var string
     */
    public $lastInsertId = '0';

    /**
     * @var array
     */
    private $errorInfo = ['', null, null];

    /**
     * @var string|null
     */
    private $errorCode = null;

    /**
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $this->attributes = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_BOTH,
        ] + $attributes;
    }

    #[\ReturnTypeWillChange]
    #[\Override]
    /**
     * @param $attribute
     * @param $value
     * @return bool
     */
    public function setAttribute($attribute, $value)
    {
        $this->attributes[$attribute] = $value;

        return true;
    }

    #[\ReturnTypeWillChange]
    #[\Override]
    /**
     * @param $attribute
     * @return mixed
     */
    public function getAttribute($attribute)
    {
        return $this->attributes[$attribute];
    }

    /**
     * @param bool $ignoreTransactions
     * @return void
     */
    public function ignoreTransactions($ignoreTransactions = true)
    {
        $this->ignoreTransactions = $ignoreTransactions;
    }

    /**
     * @param string $query
     * @return Expectation
     */
    public function expect($query)
    {
        $expectation = new Expectation($this, $query);

        $this->expectations[] = $expectation;

        return $expectation;
    }

    #[\ReturnTypeWillChange]
    #[\Override]
    /**
     * @param string $statement
     * @return int|false
     */
    public function exec($statement)
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
            if ($this->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_SILENT) {
                return false;
            }

            if ($this->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_WARNING) {
                trigger_error('PDO::exec(): ' . $expectation->exceptionOnExecute->getMessage(), E_USER_WARNING);

                return false;
            }

            if ($this->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION) {
                throw $expectation->exceptionOnExecute;
            }
        }

        if (! is_null($expectation->insertId)) {
            $this->lastInsertId = $expectation->insertId;
        }

        if ($expectation->resultSet !== null) {
            return count($expectation->resultSet->rows);
        }

        return $expectation->rowCount;
    }

    #[\ReturnTypeWillChange]
    #[\Override]
    /**
     * @param string $query
     * @param array $options
     * @return PDOStatementMock|false
     */
    public function prepare($query, $options = [])
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
            if ($this->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_SILENT) {
                return false;
            }

            if ($this->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_WARNING) {
                trigger_error('PDO::prepare(): ' . $expectation->exceptionOnPrepare->getMessage(), E_USER_WARNING);

                return false;
            }

            if ($this->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION) {
                throw $expectation->exceptionOnPrepare;
            }
        }

        $statement = new PDOStatementMock($this, $expectation, $query);

        $statement->setFetchMode(
            $this->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE),
        );

        return $statement;
    }

    #[\ReturnTypeWillChange]
    #[\Override]
    /**
     * @param string $query
     * @param $fetchMode
     * @param ...$fetch_mode_args
     * @return PDOStatement
     */
    public function query($query, $fetchMode = null, ...$fetch_mode_args)
    {
        $statement = $this->prepare($query);

        $statement->execute();

        return $statement;
    }

    /**
     * @return void
     */
    public function expectBeginTransaction()
    {
        if ($this->ignoreTransactions) {
            throw new RuntimeException('Cannot expect PDO::beginTransaction() in ignore mode.');
        }

        $this->expectations[] = new Expectation($this, 'PDO::beginTransaction()');
    }

    /**
     * @return void
     */
    public function expectCommit()
    {
        if ($this->ignoreTransactions) {
            throw new RuntimeException('Cannot expect PDO::commit() in ignore mode.');
        }

        $this->expectations[] = new Expectation($this, 'PDO::commit()');
    }

    /**
     * @return void
     */
    public function expectRollback()
    {
        if ($this->ignoreTransactions) {
            throw new RuntimeException('Cannot expect PDO::rollback() in ignore mode.');
        }

        $this->expectations[] = new Expectation($this, 'PDO::rollback()');
    }

    /**
     * @param callable $callback
     * @return void
     */
    public function expectTransaction($callback)
    {
        $this->expectBeginTransaction();

        $callback($this);

        $this->expectCommit();
    }

    #[\ReturnTypeWillChange]
    #[\Override]
    /**
     * @return bool
     */
    public function beginTransaction()
    {
        if ($this->inTransaction) {
            throw new PDOException('There is already an active transaction');
        }

        $this->inTransaction = true;

        if ($this->ignoreTransactions) {
            return true;
        }

        TestCase::assertNotEmpty($this->expectations, 'Unexpected PDO::beginTransaction()');

        $expectation = array_shift($this->expectations);

        TestCase::assertSame($expectation->query, 'PDO::beginTransaction()', 'Unexpected PDO::beginTransaction()');

        return true;
    }

    #[\ReturnTypeWillChange]
    #[\Override]
    /**
     * @return bool
     */
    public function commit()
    {
        if (! $this->inTransaction) {
            throw new PDOException('There is no active transaction');
        }

        $this->inTransaction = false;

        if ($this->ignoreTransactions) {
            return true;
        }

        TestCase::assertNotEmpty($this->expectations, 'Unexpected PDO::commit()');

        $expectation = array_shift($this->expectations);

        TestCase::assertSame($expectation->query, 'PDO::commit()', 'Unexpected PDO::commit()');

        return true;
    }

    #[\ReturnTypeWillChange]
    #[\Override]
    /**
     * @return bool
     */
    public function rollBack()
    {
        if (! $this->inTransaction) {
            throw new PDOException('There is no active transaction');
        }

        $this->inTransaction = false;

        if ($this->ignoreTransactions) {
            return true;
        }

        TestCase::assertNotEmpty($this->expectations, 'Unexpected PDO::rollback()');

        $expectation = array_shift($this->expectations);

        TestCase::assertSame($expectation->query, 'PDO::rollback()', 'Unexpected PDO::rollback()');

        return true;
    }

    #[\ReturnTypeWillChange]
    #[\Override]
    /**
     * @return bool
     */
    public function inTransaction()
    {
        return $this->inTransaction;
    }

    #[\ReturnTypeWillChange]
    #[\Override]
    /**
     * @param $name
     * @return string
     */
    public function lastInsertId($name = null)
    {
        return $this->lastInsertId;
    }

    #[\ReturnTypeWillChange]
    #[\Override]
    /**
     * @return string|null
     */
    public function errorCode()
    {
        return $this->errorCode;
    }

    #[\ReturnTypeWillChange]
    #[\Override]
    /**
     * @return array
     */
    public function errorInfo()
    {
        return $this->errorInfo;
    }

    /**
     * @return void
     */
    public function assertExpectationsFulfilled()
    {
        TestCase::assertEmpty($this->expectations, 'Some expectations were not fulfilled.');
    }
}
