<?php

namespace Xalaida\PDOMock;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

class PDOMock extends PDO
{
    const DEFAULT_FETCH_MODE = 19;

    /**
     * @var ExpectationValidatorInterface|null
     */
    public static $expectationValidator;

    /**
     * @var QueryComparatorInterface|null
     */
    public static $queryComparator;

    /**
     * @var ParamComparatorInterface|null
     */
    public static $paramComparator;

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
     * @var array{0: string|null, 1: int|string|null, 2: string|null}
     */
    protected $errorInfo = ['', null, null];

    /**
     * @var ExpectationManager
     */
    protected $expectationManager;

    /**
     * @param array<int, mixed> $attributes
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

        $this->expectationManager = new ExpectationManager();
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
     * @return void
     */
    public static function useExpectationValidator($expectationValidator)
    {
        static::$expectationValidator = $expectationValidator;
    }

    /**
     * @param QueryComparatorInterface $queryComparator
     * @return void
     */
    public static function useQueryComparator($queryComparator)
    {
        static::$queryComparator = $queryComparator;
    }

    /**
     * @param ParamComparatorInterface $paramComparator
     * @return void
     */
    public static function useParamComparator($paramComparator)
    {
        static::$paramComparator = $paramComparator;
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
        $expectation = $this->expectationManager->pushQueryExpectation($query);

        $expectation->usingQueryComparator(
            static::$queryComparator ?: new QueryComparatorExact()
        );

        $expectation->usingParamComparator(
            static::$paramComparator ?: new ParamComparatorStrict()
        );

        return $expectation;
    }

    /**
     * @param string $function
     * @return FunctionExpectation
     */
    public function expectFunction($function)
    {
        return $this->expectationManager->pushFunctionExpectation($function);
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
        $this->expectationManager->assertExpectationsFulfilled();
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
        $expectation = $this->expectationManager->pullQueryExpectation($statement);

        $expectation->assertQueryMatch($statement);
        $expectation->assertIsNotPrepared();

        if ($expectation->failException) {
            return $this->handleException($expectation->failException, 'PDO::exec()');
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
     * @param array<int, mixed> $options
     * @return PDOMockStatement
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function prepare($query, $options = [])
    {
        $this->clearErrorInfo();

        $statement = new PDOMockStatement($this, $this->expectationManager, $query);

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
    public function query($query, $fetchMode = null, ...$fetch_mode_args)
    {
        if ($fetchMode === null || $fetchMode === static::DEFAULT_FETCH_MODE) {
            $fetchMode = $this->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE);
        }

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

        $expectation = $this->expectationManager->pullFunctionExpectation('PDO::beginTransaction()');

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

        $expectation = $this->expectationManager->pullFunctionExpectation('PDO::commit()');

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

        $expectation = $this->expectationManager->pullFunctionExpectation('PDO::rollback()');

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
     * @return array{0: string|null, 1: int|string|null, 2: string|null}
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
