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

class PDOStatementMock extends PDOStatement
{
    /**
     * @var PDOMock
     */
    public $pdo;

    /**
     * @var QueryExpectation
     */
    public $expectation;

    /**
     * @var string
     */
    public $query;

    /**
     * @var array
     */
    public $params = [];

    /**
     * @var array
     */
    public $columns = [];

    /**
     * @var int
     */
    protected $fetchMode;

    /**
     * @var string|null
     */
    protected $fetchClassName;

    /**
     * @var array
     */
    protected $fetchModeParams;

    /**
     * @var int
     */
    protected $cursor = 0;

    /**
     * @var array
     */
    protected $errorInfo;

    /**
     * @var string|null
     */
    protected $errorCode;

    /**
     * @var bool
     */
    protected $executed;

    /**
     * @param PDOMock $pdo
     * @param QueryExpectation $expectation
     * @param string $query
     */
    public function __construct($pdo, $expectation, $query)
    {
        $this->pdo = $pdo;
        $this->expectation = $expectation;
        $this->query = $query;
        $this->errorInfo = ['', null, null];
        $this->errorCode = null;
        $this->fetchMode = PDO::FETCH_BOTH;
        $this->executed = false;

        // This property does not work on PHP v8.0 because it is impossible to override internally readonly property.
        if (PHP_VERSION_ID > 80100) {
            $this->queryString = $query;
        }
    }

    /**
     * @param int $mode
     * @param string|null $className
     * @param ...$params
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
            $this->fetchModeParams = $params;
        }
    }

    /**
     * @param $param
     * @param $value
     * @param $type
     * @return bool
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function bindValue($param, $value, $type = PDO::PARAM_STR)
    {
        $this->params[$param] = [
            'value' => $value,
            'type' => $type,
        ];

        return true;
    }

    /**
     * @param $param
     * @param $var
     * @param $type
     * @param $maxLength
     * @param $driverOptions
     * @return bool
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function bindParam($param, &$var, $type = PDO::PARAM_STR, $maxLength = 0, $driverOptions = null)
    {
        $this->params[$param] = [
            'value' => $var,
            'type' => $type,
        ];

        return true;
    }

    /**
     * @param $column
     * @param $var
     * @param $type
     * @param $maxLength
     * @param $driverOptions
     * @return bool
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function bindColumn($column, &$var, $type = PDO::PARAM_STR, $maxLength = 0, $driverOptions = null)
    {
        $this->columns[$column] = [
            'value' => &$var,
            'type' => $type,
        ];

        return true;
    }

    /**
     * @param array|null $params
     * @return bool
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function execute($params = null)
    {
        $params = $params !== null
            ? $this->prepareParams($params)
            : $this->params;

        $this->expectation->assertQueryMatch($this->query);
        $this->expectation->assertParamsMatch($params);
        $this->expectation->assertIsPrepared();

        $this->executed = true;

        $this->expectation->statement = $this;

        if ($this->expectation->exceptionOnExecute) {
            return $this->handleException($this->expectation->exceptionOnExecute, 'PDOStatement::execute()');
        }

        $this->clearErrorInfo();

        if (! is_null($this->expectation->insertId)) {
            $this->pdo->lastInsertId = $this->expectation->insertId;
        }

        return true;
    }

    /**
     * @param array $params
     * @return array
     */
    protected function prepareParams(array $params)
    {
        $result = [];

        foreach ($params as $key => $value) {
            $param = is_int($key)
                ? $key + 1
                : $key;

            $result[$param] = [
                'value' => $value,
                'type' => PDO::PARAM_STR,
            ];
        }

        return $result;
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
        if (! $this->executed) {
            return 0;
        }

        return $this->expectation->rowCount;
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
     * @return Iterator
     */
    #[PHP8] public function getIterator(): Iterator { /*
    public function getIterator() { # */
        if (PHP_VERSION_ID < 80000) {
            throw new RuntimeException('Method getIterator() is available only in PHP >= 8.0');
        }

        return new ArrayIterator($this->fetchAll());
    }

    /**
     * @param int $mode
     * @param $cursorOrientation
     * @param $cursorOffset
     * @return array|bool|mixed|object
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    public function fetch($mode = null, $cursorOrientation = PDO::FETCH_ORI_NEXT, $cursorOffset = 0)
    {
        if ($mode === null) {
            $mode = $this->fetchMode;
        }

        $row = false;

        if ($this->executed && isset($this->expectation->resultSet->rows[$this->cursor])) {
            $row = $this->applyFetchMode(
                $this->applyFetchColumnCase($this->expectation->resultSet->cols),
                $this->expectation->resultSet->rows[$this->cursor],
                $mode
            );

            $this->cursor++;
        }

        return $row;
    }

    /**
     * @param int|null $mode
     * @param string|null $class_name
     * @param array|null $ctor_args
     * @return array
     */
    #[\ReturnTypeWillChange]
    #[\Override]
    #[PHP8] public function fetchAll($mode = PDO::FETCH_DEFAULT, ...$args) /*
    public function fetchAll($mode = null, $class_name = null, $ctor_args = null) # */
    {
        if (PHP_VERSION_ID < 80000) {
            if ($mode === null) {
                $mode = $this->fetchMode;
            }
        } else {
            if ($mode === PDO::FETCH_DEFAULT) {
                $mode = $this->fetchMode;
            }
        }

        if ($mode === PDO::FETCH_LAZY) {
            if (PHP_VERSION_ID < 80000) {
                throw new PDOException("SQLSTATE[HY000]: General error: PDO::FETCH_LAZY can't be used with PDOStatement::fetchAll()");
            } else {
                throw new ValueError('PDOStatement::fetchAll(): Argument #1 ($mode) cannot be PDO::FETCH_LAZY in PDOStatement::fetchAll()');
            }
        }

        $allRows = [];

        if ($this->executed) {
            $cols = $this->applyFetchColumnCase($this->expectation->resultSet->cols);

            foreach ($this->expectation->resultSet->rows as $row) {
                $allRows[] = $this->applyFetchMode($cols, $row, $mode);
            }
        }

        return $allRows;
    }

    /**
     * @param array $cols
     * @return array
     */
    protected function applyFetchColumnCase($cols)
    {
        $result = [];

        if ($this->pdo->getAttribute(PDO::ATTR_CASE) === PDO::CASE_UPPER) {
            foreach ($cols as $col) {
                $result[$col] = strtoupper($col);
            }
        } else if ($this->pdo->getAttribute(PDO::ATTR_CASE) === PDO::CASE_LOWER) {
            foreach ($cols as $col) {
                $result[$col] = strtolower($col);
            }
        } else {
            $result = $cols;
        }

        return $result;
    }

    /**
     * @return bool
     */
    protected function shouldStringifyFetch()
    {
        if (PHP_VERSION_ID < 80100) {
            return true;
        }

        return $this->pdo->getAttribute(PDO::ATTR_STRINGIFY_FETCHES);
    }

    /**
     * @param array $cols
     * @param array $row
     * @param int $mode
     * @return object|array|true
     */
    protected function applyFetchMode($cols, $row, $mode)
    {
        if ($mode !== PDO::FETCH_NUM && empty($cols)) {
            throw new RuntimeException('ResultSet columns were not set.');
        }

        switch ($mode) {
            case PDO::FETCH_NUM:
                return $this->applyFetchModeNum($row);

            case PDO::FETCH_ASSOC:
                return $this->applyFetchModeAssoc($row, $cols);

            case PDO::FETCH_OBJ:
                return $this->applyFetchModeObj($row, $cols);

            case PDO::FETCH_BOTH:
                return $this->applyFetchModeBoth($row, $cols);

            case PDO::FETCH_BOUND:
                return $this->applyFetchModeBound($row, $cols);

            case PDO::FETCH_CLASS:
                return $this->applyFetchModeClass($row, $cols);

            default:
                throw new InvalidArgumentException("Unsupported fetch mode: " . $mode);
        }
    }

    /**
     * @param array $row
     * @return array
     */
    protected function applyFetchModeNum($row)
    {
        return $this->castRowValues($row);
    }

    /**
     * @param array $row
     * @param array $cols
     * @return array
     */
    protected function applyFetchModeAssoc($row, $cols)
    {
        return array_combine($cols, $this->castRowValues($row));
    }

    /**
     * @param array $row
     * @param array $cols
     * @return object
     */
    protected function applyFetchModeObj($row, $cols)
    {
        return (object) array_combine($cols, $this->castRowValues($row));
    }

    /**
     * @param array $row
     * @param array $cols
     * @return array
     */
    protected function applyFetchModeBoth($row, $cols)
    {
        $values = $this->castRowValues($row);

        return array_merge($values, array_combine($cols, $values));
    }

    /**
     * @param array $row
     * @param array $cols
     * @return bool
     */
    protected function applyFetchModeBound($row, $cols)
    {
        foreach ($this->columns as $column => $params) {
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
                $params['value'] = null;
            } else {
                $params['value'] = $this->castRowValue($row[$index], $params['type']);
            }
        }

        return true;
    }

    /**
     * @param array $row
     * @param array $cols
     * @return object
     * @throws \ReflectionException
     */
    protected function applyFetchModeClass($row, $cols)
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
                $prop->setValue($classInstance, $this->castRowValue($row[$key]));
            }
        }

        $constructor = $reflectionClass->getConstructor();

        if ($constructor) {
            $constructor->invokeArgs($classInstance, ...$this->fetchModeParams);
        }

        return $classInstance;
    }

    /**
     * @param array $row
     * @return array
     */
    protected function castRowValues($row)
    {
        $result = [];

        foreach ($row as $key => $value) {
            $result[$key] = $this->castRowValue($value);
        }

        return $result;
    }

    /**
     * @param mixed $value
     * @param int|null $type
     * @return mixed
     */
    protected function castRowValue($value, $type = null)
    {
        if ($this->pdo->getAttribute(PDO::ATTR_ORACLE_NULLS) === PDO::NULL_EMPTY_STRING && $value === '') {
            $value = null;
        }

        $shouldStringify = $this->pdo->getAttribute(PDO::ATTR_STRINGIFY_FETCHES);

        if ($shouldStringify !== true) {
            $shouldStringify = PHP_VERSION_ID < 80100;
        }

        if ($value !== null && $type === null && $shouldStringify) {
            $type = PDO::PARAM_STR;
        }

        if ($type === PDO::PARAM_INT && $this->pdo->getAttribute(PDO::ATTR_STRINGIFY_FETCHES)) {
            $type = PDO::PARAM_STR;
        }

        if ($type !== null) {
            switch ($type) {
                case PDO::PARAM_NULL:
                    $value = null;
                    break;

                case PDO::PARAM_INT:
                    $value = (int) $value;
                    break;

                case PDO::PARAM_STR:
                    if (!($value === null && $this->pdo->getAttribute(PDO::ATTR_ORACLE_NULLS) === PDO::NULL_EMPTY_STRING)) {
                        $value = (string) $value;
                    }
                    break;

                case PDO::PARAM_BOOL:
                    $value = (bool) $value;
                    break;

                default:
                    throw new InvalidArgumentException('Unsupported column type: ' . $type);
            }
        }

        if ($value === null && $this->pdo->getAttribute(PDO::ATTR_ORACLE_NULLS) === PDO::NULL_TO_STRING) {
            $value = '';
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @param int $type
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
                throw new InvalidArgumentException('Unsupported column type: ' . $type);
        }
    }
}
