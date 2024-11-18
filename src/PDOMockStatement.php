<?php

namespace Xalaida\PDOMock;

use ArrayIterator;
use InvalidArgumentException;
use Iterator;
use PDO;
use PDOException;
use PDOStatement;
use ReflectionClass;
use RuntimeException;
use ValueError;

class PDOMockStatement extends PDOStatement
{
    /**
     * @var string
     */
    public $query;

    /**
     * @var array<int|string, mixed>
     */
    public $params = [];

    /**
     * @var array<int|string, int>
     */
    public $types = [];

    /**
     * @var array<int|string, array{ref: mixed, type: int}>
     */
    protected $cols = [];

    /**
     * @var array<int, mixed>
     */
    protected $attributes = [];

    /**
     * @var int
     */
    protected $fetchMode = PDO::FETCH_BOTH;

    /**
     * @var string|null
     */
    protected $fetchClassName;

    /**
     * @var array<int, mixed>
     */
    protected $fetchParams = [];

    /**
     * @var array{0: string|null, 1: int|string|null, 2: string|null}
     */
    protected $errorInfo = ['', null, null];

    /**
     * @var string|null
     */
    protected $errorCode;

    /**
     * @var int
     */
    protected $rowCount = 0;

    /**
     * @var ResultSetIterator|null
     */
    protected $resultSetIterator;

    /**
     * @var bool
     */
    protected $executed = false;

    /**
     * @var ExpectationManager
     */
    protected $expectationManager;

    /**
     * @var PDOMock
     */
    protected $pdo;

    /**
     * @param PDOMock $pdo
     * @param ExpectationManager $expectationManager
     * @param string $query
     */
    public function __construct($pdo, $expectationManager, $query)
    {
        $this->pdo = $pdo;
        $this->expectationManager = $expectationManager;
        $this->query = $query;

        if (PHP_VERSION_ID > 80100) {
            $this->queryString = $query;
        }
    }

    /**
     * @param int $attribute
     * @param mixed $value
     * @return true
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function setAttribute($attribute, $value)
    {
        $this->attributes[$attribute] = $value;

        return true;
    }

    /**
     * @param int $name
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function getAttribute($name)
    {
        if (! isset($this->attributes[$name])) {
            return null;
        }

        return $this->attributes[$name];
    }

    /**
     * @param int $mode
     * @param string|null $className
     * @param array<int, mixed> ...$params
     * @return void
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function setFetchMode($mode, $className = null, ...$params)
    {
        $this->fetchMode = $mode;

        if ($className) {
            $this->fetchClassName = $className;
        }

        if ($params) {
            $this->fetchParams = $params[0];
        }
    }

    /**
     * @param int|string $param
     * @param mixed $value
     * @param int $type
     * @return bool
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function bindValue($param, $value, $type = PDO::PARAM_STR)
    {
        if ($param === 0) {
            if (PHP_VERSION_ID < 80000) {
                throw new PDOException('SQLSTATE[HY093]: Invalid parameter number: Columns/Parameters are 1-based');
            } else {
                throw new ValueError('PDOStatement::bindValue(): Argument #1 ($param) must be greater than or equal to 1');
            }
        }

        $this->params[$param] = $value;
        $this->types[$param] = $type;

        return true;
    }

    /**
     * @param int|string $param
     * @param mixed $var
     * @param int $type
     * @param int $maxLength
     * @param mixed $driverOptions
     * @return bool
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function bindParam($param, &$var, $type = PDO::PARAM_STR, $maxLength = 0, $driverOptions = null)
    {
        if ($param === 0) {
            if (PHP_VERSION_ID < 80000) {
                throw new PDOException('SQLSTATE[HY093]: Invalid parameter number: Columns/Parameters are 1-based');
            } else {
                throw new ValueError('PDOStatement::bindParam(): Argument #1 ($param) must be greater than or equal to 1');
            }
        }

        $this->params[$param] = $var;
        $this->types[$param] = $type;

        return true;
    }

    /**
     * @param int|string $column
     * @param mixed $var
     * @param int $type
     * @param int $maxLength
     * @param mixed $driverOptions
     * @return bool`
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function bindColumn($column, &$var, $type = PDO::PARAM_STR, $maxLength = 0, $driverOptions = null)
    {
        if ($column === 0) {
            if (PHP_VERSION_ID < 80000) {
                throw new PDOException('SQLSTATE[HY093]: Invalid parameter number: Columns/Parameters are 1-based');
            } else {
                throw new ValueError('PDOStatement::bindColumn(): Argument #1 ($column) must be greater than or equal to 1');
            }
        }

        $this->cols[$column] = [
            'ref' => &$var,
            'type' => $type,
        ];

        return true;
    }

    /**
     * @param array<int|string, mixed>|null $params
     * @return bool
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function execute($params = null)
    {
        if ($params !== null) {
            $normalizedParams = [];
            $normalizedTypes = [];

            foreach ($params as $key => $value) {
                $param = is_int($key)
                    ? $key + 1
                    : $key;

                $normalizedParams[$param] = $value;
                $normalizedTypes[$param] = PDO::PARAM_STR;
            }
        } else {
            $normalizedParams = $this->params;
            $normalizedTypes = $this->types;
        }

        $expectation = $this->expectationManager->pullQueryExpectation($this->query);

        $expectation->assertQueryMatch($this->query);
        $expectation->assertParamsMatch($normalizedParams, $normalizedTypes);
        $expectation->assertIsPrepared();

        if ($expectation->failException) {
            return $this->handleException($expectation->failException, 'PDOStatement::execute()');
        }

        $this->clearErrorInfo();

        if ($expectation->resultSet !== null) {
            $this->resultSetIterator = ResultSetIterator::fromResultSet($expectation->resultSet);
        }

        if (! is_null($expectation->insertId)) {
            $this->pdo->lastInsertId = $expectation->insertId;
        }

        $this->rowCount = $expectation->rowCount;

        $expectation->statement = $this;

        $this->executed = true;

        return true;
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

        if ($this->pdo->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION) {
            throw $exception;
        }

        if ($this->pdo->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_WARNING) {
            trigger_error($function . ': ' . $exception->getMessage(), E_USER_WARNING);
        }

        return false;
    }

    /**
     * @return int
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function rowCount()
    {
        return $this->rowCount;
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
     * @param int $mode
     * @param int $cursorOrientation
     * @param int $cursorOffset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function fetch($mode = null, $cursorOrientation = PDO::FETCH_ORI_NEXT, $cursorOffset = 0)
    {
        if ($mode === null) {
            $mode = $this->fetchMode;
        }

        if (! $this->executed) {
            return false;
        }

        if ($this->resultSetIterator === null) {
            throw new RuntimeException('ResultSet was not set. Use "willFetch" method to specify fetch results.');
        }

        if (! $this->resultSetIterator->valid()) {
            return false;
        }

        $row = $this->applyFetchMode(
            $mode,
            $this->resultSetIterator->current(),
            $this->applyFetchColumnCase($this->resultSetIterator->cols())
        );

        $this->resultSetIterator->next();

        return $row;
    }

    /**
     * @param int $column
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function fetchColumn($column = 0)
    {
        if (! $this->executed) {
            return false;
        }

        if ($this->resultSetIterator === null) {
            throw new RuntimeException('ResultSet was not set. Use "willFetch" method to specify fetch results.');
        }

        if (! $this->resultSetIterator->valid()) {
            return false;
        }

        $row = $this->resultSetIterator->current();

        if (! isset($row[$column])) {
            if (PHP_VERSION_ID < 80000) {
                throw new PDOException('Invalid column index'); // TODO: update exception message
            } else {
                throw new ValueError('Invalid column index');
            }
        }

        $columnValue = $this->castColumnValue($row[$column]);

        $this->resultSetIterator->next();

        return $columnValue;
    }

    /**
     * @param int|null $mode
     * @return array<int, mixed>
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    // @phpstan-ignore-next-line
    #[PHP8] public function fetchAll($mode = PDO::FETCH_DEFAULT, ...$params) /* Compatible with PHP >= 8
    public function fetchAll($mode = null, $className = null, $params = null) # Compatible with PHP < 8 */
    {
        if (PHP_VERSION_ID < 80000) {
            if ($mode === null) {
                $mode = $this->fetchMode;
            } else {
                // @phpstan-ignore-next-line
                $this->fetchClassName = $className;
                $this->fetchParams = $params;
            }
        } else {
            if ($mode === PDO::FETCH_DEFAULT) {
                $mode = $this->fetchMode;
            } else {
                $this->fetchClassName = isset($params[0])
                    ? $params[0]
                    : null;
                $this->fetchParams = isset($params[1])
                    ? $params[1]
                    : [];
            }
        }

        if ($mode === PDO::FETCH_LAZY) {
            if (PHP_VERSION_ID < 80000) {
                throw new PDOException("SQLSTATE[HY000]: General error: PDO::FETCH_LAZY can't be used with PDOStatement::fetchAll()");
            } else {
                throw new ValueError('PDOStatement::fetchAll(): Argument #1 ($mode) cannot be PDO::FETCH_LAZY in PDOStatement::fetchAll()');
            }
        }

        $rows = [];

        if (! $this->executed) {
            return $rows;
        }

        if ($this->resultSetIterator === null) {
            throw new RuntimeException('ResultSet was not set. Use "willFetch" method to specify fetch results.');
        }

        if ($this->resultSetIterator->valid()) {
            $cols = $this->applyFetchColumnCase($this->resultSetIterator->cols());

            foreach ($this->resultSetIterator as $row) {
                $rows[] = $this->applyFetchMode($mode, $row, $cols);
            }
        }

        return $rows;
    }

    /**
     * @return Iterator
     */
    // @phpstan-ignore-next-line
    #[PHP8] public function getIterator(): Iterator { /* Compatible with PHP >= 8
    public function getIterator() { # Compatible with PHP < 8 */
        if (PHP_VERSION_ID < 80000) {
            throw new RuntimeException('Method getIterator() is available only in PHP >= 8.0');
        }

        return new ArrayIterator($this->fetchAll());
    }

    /**
     * @return true
     */
    #[\Override]
    #[\ReturnTypeWillChange]
    public function closeCursor()
    {
        if ($this->resultSetIterator) {
            $this->resultSetIterator->close();
        }

        return true;
    }

    /**
     * @param array<int|string> $cols
     * @return array<int|string>
     */
    protected function applyFetchColumnCase($cols)
    {
        $result = [];

        if ($this->pdo->getAttribute(PDO::ATTR_CASE) === PDO::CASE_UPPER) {
            foreach ($cols as $col) {
                $result[$col] = strtoupper($col);
            }
        } elseif ($this->pdo->getAttribute(PDO::ATTR_CASE) === PDO::CASE_LOWER) {
            foreach ($cols as $col) {
                $result[$col] = strtolower($col);
            }
        } else {
            $result = $cols;
        }

        return $result;
    }

    /**
     * @param int $mode
     * @param array<int, mixed> $row
     * @param array<int|string> $cols
     * @return mixed
     */
    protected function applyFetchMode($mode, $row, $cols)
    {
        if ($mode !== PDO::FETCH_NUM && empty($cols)) {
            throw new RuntimeException('ResultSet columns were not set.');
        }

        if ($mode === PDO::FETCH_NUM) {
            return $this->applyFetchModeNum($row);
        }

        if ($mode === PDO::FETCH_ASSOC) {
            return $this->applyFetchModeAssoc($row, $cols);
        }

        if ($mode === PDO::FETCH_OBJ) {
            return $this->applyFetchModeObj($row, $cols);
        }

        if ($mode === PDO::FETCH_BOTH) {
            return $this->applyFetchModeBoth($row, $cols);
        }

        if ($mode === PDO::FETCH_BOUND) {
            return $this->applyFetchModeBound($row, $cols);
        }

        if ($mode === PDO::FETCH_CLASS) {
            return $this->applyFetchModeClassEarlyProps($row, $cols);
        }

        if (($mode & (PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE)) === (PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE)) {
            return $this->applyFetchModeClassLateProps($row, $cols);
        }

        throw new InvalidArgumentException("Unsupported fetch mode: " . $mode);
    }

    /**
     * @param array<int, mixed> $row
     * @return array<int, mixed>
     */
    protected function applyFetchModeNum($row)
    {
        return $this->castColumnValues($row);
    }

    /**
     * @param array<int, mixed> $row
     * @param array<int|string> $cols
     * @return array<int|string, mixed>
     */
    protected function applyFetchModeAssoc($row, $cols)
    {
        return array_combine($cols, $this->castColumnValues($row));
    }

    /**
     * @param array<int, mixed> $row
     * @param array<int|string> $cols
     * @return object
     */
    protected function applyFetchModeObj($row, $cols)
    {
        return (object) array_combine($cols, $this->castColumnValues($row));
    }

    /**
     * @param array<int, mixed> $row
     * @param array<int|string> $cols
     * @return array<int, mixed>
     */
    protected function applyFetchModeBoth($row, $cols)
    {
        $values = $this->castColumnValues($row);

        return array_merge($values, array_combine($cols, $values));
    }

    /**
     * @param array<int, mixed> $row
     * @param array<int|string> $cols
     * @return bool
     */
    protected function applyFetchModeBound($row, $cols)
    {
        foreach ($this->cols as $column => $params) {
            if (is_int($column)) {
                $index = $column - 1;

                if (! isset($row[$index])) {
                    if (PHP_VERSION_ID < 80000) {
                        throw new PDOException("SQLSTATE[HY000]: General error: Invalid column index");
                    } else {
                        throw new ValueError('Invalid column index');
                    }
                }
            } else {
                $index = array_search($column, $cols, true);
            }

            if ($index === false) {
                $params['ref'] = null;
            } else {
                $params['ref'] = $this->castColumnValue($row[$index], $params['type']);
            }
        }

        return true;
    }

    /**
     * @param array<int, mixed> $row
     * @param array<int|string> $cols
     * @return mixed
     */
    protected function applyFetchModeClassEarlyProps($row, $cols)
    {
        if (! $this->fetchClassName) {
            throw new PDOException('PDOException: SQLSTATE[HY000]: General error: No fetch class specified');
        }

        $reflectionClass = new ReflectionClass($this->fetchClassName);

        $classInstance = $reflectionClass->newInstanceWithoutConstructor();

        foreach ($cols as $key => $col) {
            if ($reflectionClass->hasProperty($col)) {
                $prop = $reflectionClass->getProperty($col);
                $prop->setAccessible(true);
                $prop->setValue($classInstance, $this->castColumnValue($row[$key]));
            }
        }

        $constructor = $reflectionClass->getConstructor();

        if ($constructor) {
            $constructor->invokeArgs($classInstance, $this->fetchParams);
        }

        return $classInstance;
    }

    /**
     * @param array<int, mixed> $row
     * @param array<int|string> $cols
     * @return mixed
     */
    protected function applyFetchModeClassLateProps($row, $cols)
    {
        if (! $this->fetchClassName) {
            throw new PDOException('PDOException: SQLSTATE[HY000]: General error: No fetch class specified');
        }

        $reflectionClass = new ReflectionClass($this->fetchClassName);

        $classInstance = $reflectionClass->newInstanceArgs($this->fetchParams);

        foreach ($cols as $key => $col) {
            if ($reflectionClass->hasProperty($col)) {
                $prop = $reflectionClass->getProperty($col);
                $prop->setAccessible(true);
                $prop->setValue($classInstance, $this->castColumnValue($row[$key]));
            }
        }

        return $classInstance;
    }

    /**
     * @param array<int, mixed> $row
     * @return array<int, mixed>
     */
    protected function castColumnValues($row)
    {
        $result = [];

        foreach ($row as $column => $value) {
            $result[$column] = $this->castColumnValue($value);
        }

        return $result;
    }

    /**
     * @param mixed $value
     * @param int|null $type
     * @return mixed
     */
    protected function castColumnValue($value, $type = null)
    {
        if ($value === '' && $this->pdo->getAttribute(PDO::ATTR_ORACLE_NULLS) === PDO::NULL_EMPTY_STRING) {
            return null;
        }

        if ($value !== null && $this->shouldOverrideParamTypeToString($type)) {
            $type = PDO::PARAM_STR;
        }

        if ($type !== null) {
            $value = $this->applyParamType($value, $type);
        }

        if ($value === null && $this->pdo->getAttribute(PDO::ATTR_ORACLE_NULLS) === PDO::NULL_TO_STRING) {
            return '';
        }

        return $value;
    }

    /**
     * @param int|null $type
     * @return bool
     */
    protected function shouldOverrideParamTypeToString($type)
    {
        if ($type === PDO::PARAM_INT && $this->pdo->getAttribute(PDO::ATTR_STRINGIFY_FETCHES)) {
            return true;
        }

        if ($this->pdo->getAttribute(PDO::ATTR_STRINGIFY_FETCHES) && PHP_VERSION_ID >= 80100) {
            return true;
        }

        if (PHP_VERSION_ID < 80100) {
            return $type === null;
        }

        return $this->pdo->getAttribute(PDO::ATTR_STRINGIFY_FETCHES) !== false;
    }

    /**
     * @param mixed $value
     * @param int|null $type
     * @return mixed
     */
    protected function applyParamType($value, $type)
    {
        switch ($type) {
            case PDO::PARAM_NULL:
                return null;

            case PDO::PARAM_INT:
                return (int) $value;

            case PDO::PARAM_STR:
                return (string) $value;

            case PDO::PARAM_BOOL:
                return (bool) $value;

            default:
                return $value;
        }
    }
}
