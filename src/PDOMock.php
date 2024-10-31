<?php

namespace Xalaida\PDOMock;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

class PDOMock extends PDO
{
    /**
     * @var AssertionManager|null
     */
    public $assertionManager;

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
    public function __construct($dsn = 'mock', $attributes = [])
    {
        $this->attributes = [
            PDO::ATTR_DRIVER_NAME => $dsn,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_BOTH,
            PDO::ATTR_STRINGIFY_FETCHES => PHP_VERSION_ID < 80000,
        ] + $attributes;
    }

    /**
     * @param AssertionManager|null $assertionManager
     * @return void
     */
    public function setAssertionManager($assertionManager = null)
    {
        $this->assertionManager = $assertionManager;
    }

    /**
     * @param $attribute
     * @param $value
     * @return bool
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function setAttribute($attribute, $value)
    {
        $this->attributes[$attribute] = $value;

        return true;
    }

    /**
     * @param $attribute
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    #[\Override]
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

    /**
     * @param string $statement
     * @return int|false
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function exec($statement)
    {
        $expectation = $this->getExpectationForQuery($statement);

        if ($expectation->query !== $statement) {
            throw new RuntimeException('Unexpected query: ' . $statement);
        }

        if ($expectation->prepared === true) {
            throw new RuntimeException('Statement is not prepared');
        }

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

    /**
     * @param string $query
     * @param array $options
     * @return PDOStatementMock|false
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function prepare($query, $options = [])
    {
        $expectation = $this->getExpectationForQuery($query);

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
            $this->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE)
        );

        return $statement;
    }

    /**
     * @param string $query
     * @param $fetchMode
     * @param ...$fetch_mode_args
     * @return PDOStatement
     */
    #[\ReturnTypeWillChange]
    #[\Override]
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

    /**
     * @return bool
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function beginTransaction()
    {
        if ($this->inTransaction) {
            throw new PDOException('There is already an active transaction');
        }

        $this->inTransaction = true;

        if ($this->ignoreTransactions) {
            return true;
        }

        $this->assertFunctionIsExpected('PDO::beginTransaction()');

        return true;
    }

    /**
     * @return bool
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function commit()
    {
        if (! $this->inTransaction) {
            throw new PDOException('There is no active transaction');
        }

        $this->inTransaction = false;

        if ($this->ignoreTransactions) {
            return true;
        }

        $this->assertFunctionIsExpected('PDO::commit()');

        return true;
    }

    /**
     * @return bool
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function rollBack()
    {
        if (! $this->inTransaction) {
            throw new PDOException('There is no active transaction');
        }

        $this->inTransaction = false;

        if ($this->ignoreTransactions) {
            return true;
        }

        $this->assertFunctionIsExpected('PDO::rollback()');

        return true;
    }

    /**
     * @return bool
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function inTransaction()
    {
        return $this->inTransaction;
    }

    /**
     * @param $name
     * @return string
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function lastInsertId($name = null)
    {
        return $this->lastInsertId;
    }

    /**
     * @return string|null
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function errorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return array
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function errorInfo()
    {
        return $this->errorInfo;
    }

    /**
     * @return void
     */
    public function assertExpectationsFulfilled()
    {
        if ($this->assertionManager) {
            $this->assertionManager->incrementAssertions();
        }

        if (! empty($this->expectations)) {
            throw new RuntimeException('Some expectations were not fulfilled.');
        }
    }

    /**
     * @param string $query
     * @return Expectation
     * @throws \UnexpectedValueException
     */
    protected function getExpectationForQuery($query)
    {
        if (empty($this->expectations)) {
            throw new \RuntimeException('Unexpected query: ' . $query);
        }

        return array_shift($this->expectations);
    }

    /**
     * @param string $function
     * @return void
     */
    protected function assertFunctionIsExpected($function)
    {
        if (empty($this->expectations)) {
            throw new \RuntimeException('Unexpected function: ' . $function);
        }

        $expectation = array_shift($this->expectations);

        if ($expectation->query !== $function) {
            throw new \RuntimeException('Unexpected function: ' . $function);
        }
    }
}
