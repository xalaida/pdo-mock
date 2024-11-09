<?php

namespace Xalaida\PDOMock;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

class PDOMock extends PDO
{
    /**
     * @var ExpectationValidatorInterface
     */
    public static $expectationValidator;

    /**
     * @var QueryComparatorInterface|null
     */
    public static $defaultQueryComparator;

    /**
     * @var array<int, QueryExpectation|FunctionExpectation>
     */
    public $expectations = [];

    /**
     * @var bool
     */
    public $ignoreTransactions = false;

    /**
     * @var string
     */
    public $lastInsertId = '0';

    /**
     * @var array<int, int>
     */
    protected $attributes;

    /**
     * @var bool
     */
    protected $inTransaction = false;

    /**
     * @var string|null
     */
    protected $errorCode;

    /**
     * @var array
     */
    protected $errorInfo = ['', null, null];

    /**
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $this->attributes = [
            PDO::ATTR_DRIVER_NAME => 'mock',
            PDO::ATTR_SERVER_VERSION => '1.0.0',
            PDO::ATTR_CLIENT_VERSION => '1.0.0',
            PDO::ATTR_ERRMODE => PHP_VERSION_ID < 80000
                ? PDO::ERRMODE_SILENT
                : PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STATEMENT_CLASS => [PDOStatement::class],
            PDO::ATTR_STRINGIFY_FETCHES => PHP_VERSION_ID < 80100
                ? null
                : false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_BOTH,
        ] + $attributes;
    }

    /**
     * @param AdapterInterface $adapter
     * @return void
     */
    public static function useAdapter($adapter)
    {
        $adapter->configure();
    }

    /**
     * @param ExpectationValidatorInterface $expectationValidator
     */
    public static function useExpectationValidator($expectationValidator)
    {
        static::$expectationValidator = $expectationValidator;
    }

    /**
     * @return ExpectationValidatorInterface
     */
    public static function getExpectationValidator()
    {
        return static::$expectationValidator ?: new ExpectationValidator();
    }

    /**
     * @param QueryComparatorInterface $defaultQueryComparator
     */
    public static function setDefaultQueryComparator($defaultQueryComparator)
    {
        static::$defaultQueryComparator = $defaultQueryComparator;
    }

    /**
     * @return QueryComparatorInterface
     */
    public static function getDefaultQueryComparator()
    {
        return static::$defaultQueryComparator ?: new QueryComparatorExact();
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
     * @return QueryExpectation
     */
    public function expectQuery($query)
    {
        $expectation = new QueryExpectation(
            static::getExpectationValidator(),
            static::getDefaultQueryComparator(),
            $query
        );

        $this->expectations[] = $expectation;

        return $expectation;
    }

    /**
     * @param string $function
     * @return FunctionExpectation
     */
    public function expectFunction($function)
    {
        $expectation = new FunctionExpectation(static::getExpectationValidator(), $function);

        $this->expectations[] = $expectation;

        return $expectation;
    }

    /**
     * @param string $query
     * @return QueryExpectation
     * @throws ExpectationFailedException
     */
    public function getExpectationForQuery($query)
    {
        if (empty($this->expectations)) {
            throw new ExpectationFailedException('Unexpected query: ' . $query);
        }

        $expectation = array_shift($this->expectations);

        if (! $expectation instanceof QueryExpectation) {
            throw new ExpectationFailedException('Unexpected query: ' . $query);
        }

        return $expectation;
    }

    /**
     * @param string $function
     * @return FunctionExpectation
     * @throws ExpectationFailedException
     */
    public function getExpectationForFunction($function)
    {
        if (empty($this->expectations)) {
            throw new ExpectationFailedException('Unexpected function: ' . $function);
        }

        $expectation = array_shift($this->expectations);

        if (! $expectation instanceof FunctionExpectation) {
            throw new ExpectationFailedException('Unexpected function: ' . $function);
        }

        return $expectation;
    }

    /**
     * @param string $query
     * @return QueryExpectation
     */
    public function expect($query)
    {
        return $this->expectQuery($query);
    }

    /**
     * @return void
     */
    public function expectBeginTransaction()
    {
        if ($this->ignoreTransactions) {
            throw new RuntimeException('Cannot expect PDO::beginTransaction() in ignore mode.');
        }

        $this->expectFunction('PDO::beginTransaction()');
    }

    /**
     * @return void
     */
    public function expectCommit()
    {
        if ($this->ignoreTransactions) {
            throw new RuntimeException('Cannot expect PDO::commit() in ignore mode.');
        }

        $this->expectFunction('PDO::commit()');
    }

    /**
     * @return void
     */
    public function expectRollback()
    {
        if ($this->ignoreTransactions) {
            throw new RuntimeException('Cannot expect PDO::rollback() in ignore mode.');
        }

        $this->expectFunction('PDO::rollback()');
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
     * @return void
     */
    public function assertExpectationsFulfilled()
    {
        if (! empty($this->expectations)) {
            throw new ExpectationFailedException('Some expectations were not fulfilled.');
        }
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
     * @throws PDOException
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function getAttribute($attribute)
    {
        if (! isset($this->attributes[$attribute])) {
            return null;
            // throw new PDOException('SQLSTATE[IM001]: Driver does not support this function: driver does not support that attribute');
        }

        return $this->attributes[$attribute];
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

        $expectation->assertQueryMatch($statement);
        $expectation->assertIsNotPrepared();

        if ($expectation->exceptionOnExecute) {
            return $this->handleException($expectation->exceptionOnExecute, 'PDO::exec()');
        }

        $this->clearErrorInfo();

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

        if ($expectation->exceptionOnPrepare) {
            return $this->handleException($expectation->exceptionOnPrepare, 'PDO::prepare()');
        }

        $this->clearErrorInfo();

        $statement = new PDOStatementMock($this, $expectation, $query);

        $statement->setFetchMode(
            $this->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE)
        );

        return $statement;
    }

    /**
     * @param string $query
     * @param int $fetchMode
     * @param ...$fetch_mode_args
     * @return PDOStatement
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function query($query, $fetchMode = PDO::ATTR_DEFAULT_FETCH_MODE, ...$fetch_mode_args)
    {
        $statement = $this->prepare($query);

        $statement->setFetchMode($fetchMode, ...$fetch_mode_args);

        $statement->execute();

        return $statement;
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

        $expectation = $this->getExpectationForFunction('PDO::beginTransaction()');

        $expectation->assertFunctionMatch('PDO::beginTransaction()');

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

        $expectation = $this->getExpectationForFunction('PDO::commit()');

        $expectation->assertFunctionMatch('PDO::commit()');

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

        $expectation = $this->getExpectationForFunction('PDO::rollback()');

        $expectation->assertFunctionMatch('PDO::rollback()');

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
    protected function clearErrorInfo()
    {
        $this->errorCode = PDO::ERR_NONE;
        $this->errorInfo = [PDO::ERR_NONE, null, null];
    }

    /**
     * @param PDOException $exception
     * @param string $function
     * @return false
     * @throws PDOException
     */
    protected function handleException($exception, $function)
    {
        if ($exception->errorInfo) {
            $this->errorInfo = $exception->errorInfo;
            $this->errorCode = $exception->errorInfo[0];
        }

        if ($this->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION) {
            throw $exception;
        }

        if ($this->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_WARNING) {
            trigger_error($function . ': ' . $exception->getMessage(), E_USER_WARNING);
        }

        return false;
    }
}
