<?php

namespace Xalaida\PDOMock;

use Closure;
use InvalidArgumentException;
use PDO;
use PDOException;

class QueryExpectation
{
    /**
     * @var ExpectationValidatorInterface
     */
    public $expectationValidator;

    /**
     * @var QueryComparatorInterface
     */
    public $queryComparator;

    /**
     * @var ParamsComparator
     */
    public $paramsComparator;

    /**
     * @var string
     */
    public $query;

    /**
     * @var array<int|string, array{value: mixed, type: int}>|Closure|null
     */
    public $params;

    /**
     * @var bool|null
     */
    public $prepared;

    /**
     * @var int
     */
    public $rowCount = 0;

    /**
     * @var ResultSet|null
     */
    public $resultSet;

    /**
     * @var string|null
     */
    public $insertId;

    /**
     * @var PDOException|null
     */
    public $exceptionOnExecute;

    /**
     * @var PDOException|null
     */
    public $exceptionOnPrepare;

    /**
     * @var PDOStatementMock|null
     */
    public $statement;

    /**
     * @param ExpectationValidatorInterface $expectationValidator
     * @param QueryComparatorInterface $queryComparator
     * @param string $query
     */
    public function __construct($expectationValidator, $queryComparator, $query)
    {
        $this->expectationValidator = $expectationValidator;
        $this->queryComparator = $queryComparator;
        $this->paramsComparator = new ParamsComparator();
        $this->query = $query;
    }

    /**
     * @param QueryComparatorInterface $queryComparator
     * @return $this
     */
    public function usingQueryComparator($queryComparator)
    {
        $this->queryComparator = $queryComparator;

        return $this;
    }

    /**
     * @return $this
     */
    public function toMatchRegex()
    {
        $this->queryComparator = new QueryComparatorRegex();

        return $this;
    }

    /**
     * @return $this
     */
    public function toBeExact()
    {
        $this->queryComparator = new QueryComparatorExact();

        return $this;
    }

    /**
     * @param bool $prepared
     * @return $this
     */
    public function toBePrepared($prepared = true)
    {
        $this->prepared = $prepared;

        return $this;
    }

    /**
     * @param array<int|string, int|string>|Closure $params
     * @param array<int|string, int> $types
     * @return $this
     */
    public function with($params, $types = [])
    {
        if (is_callable($params)) {
            return $this->withParamsUsing($params);
        }

        $this->withParams($params);

        if (is_array($types)) {
            $this->withTypes($types);
        }

        return $this;
    }

    /**
     * @param string|int $param
     * @param mixed $value
     * @param int $type
     * @return $this
     */
    public function withParam($param, $value, $type = PDO::PARAM_STR)
    {
        $this->params[$param] = [
            'value' => $value,
            'type' => $type,
        ];

        return $this;
    }

    /**
     * @param array<int|string, int|string> $params
     * @param bool $useParamValueType
     * @return $this
     */
    public function withParams($params, $useParamValueType = false)
    {
        foreach ($params as $key => $value) {
            $param = is_int($key)
                ? $key + 1
                : $key;

            $type = $useParamValueType
                ? $this->getTypeFromValue($value)
                : PDO::PARAM_STR;

            $this->withParam($param, $value, $type);
        }

        return $this;
    }

    /**
     * @param array<int|string, int> $types
     * @return void
     */
    public function withTypes($types)
    {
        foreach ($types as $key => $type) {
            $param = is_int($key)
                ? $key + 1
                : $key;

            if (! isset($this->params[$param])) {
                throw new InvalidArgumentException("Param is not set: " . $param);
            }

            $this->params[$param]['type'] = $type;
        }
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function withParamsUsing($callback)
    {
        $this->params = $callback;

        return $this;
    }

    /**
     * @param mixed $value
     * @return int
     */
    protected function getTypeFromValue($value)
    {
        $type = gettype($value);

        switch ($type) {
            case 'string':
                return PDO::PARAM_STR;

            case 'integer':
                return PDO::PARAM_INT;

            case 'boolean':
                return PDO::PARAM_BOOL;

            default:
                throw new InvalidArgumentException('Unsupported type: ' . $type);
        }
    }

    /**
     * @param int|string $insertId
     * @return $this
     */
    public function withInsertId($insertId)
    {
        $this->insertId = (string) $insertId;

        return $this;
    }

    /**
     * @param int $rowCount
     * @return $this
     */
    public function affecting($rowCount)
    {
        $this->rowCount = $rowCount;

        return $this;
    }

    /**
     * @param ResultSet|array<array<int|string, int|string>> $resultSet
     * @return $this
     */
    public function andFetch($resultSet)
    {
        if (is_array($resultSet)) {
            return $this->andFetchRows($resultSet);
        }

        return $this->andFetchResultSet($resultSet);
    }

    /**
     * @param ResultSet $resultSet
     * @return $this
     */
    public function andFetchResultSet($resultSet)
    {
        $this->resultSet = $resultSet;

        return $this;
    }

    /**
     * @param array<array<int|string, int|string>> $rows
     * @return $this
     */
    public function andFetchRows($rows)
    {
        return $this->andFetchResultSet(
            ResultSet::fromArray($rows)
        );
    }

    /**
     * @param array<int|string, int|string> $row
     * @return $this
     */
    public function andFetchRow($row)
    {
        return $this->andFetchResultSet(
            ResultSet::fromArray([
                $row,
            ])
        );
    }

    /**
     * @param PDOException $exception
     * @return $this
     */
    public function andFailOnExecute($exception)
    {
        $this->exceptionOnExecute = $exception;

        return $this;
    }

    /**
     * @param PDOException $exception
     * @return $this
     */
    public function andFailOnPrepare($exception)
    {
        $this->exceptionOnPrepare = $exception;

        return $this;
    }

    /**
     * @param string $query
     * @return void
     */
    public function assertQueryMatch($query)
    {
        $this->expectationValidator->assertQueryMatch($this, $query);
    }

    /**
     * @param array<int|string, array{value: mixed, type: int}> $params
     * @return void
     */
    public function assertParamsMatch($params)
    {
        if (! is_null($this->params)) {
            $this->expectationValidator->assertParamsMatch($this, $params);
        }
    }

    /**
     * @return void
     */
    public function assertIsPrepared()
    {
        if (! is_null($this->prepared)) {
            $this->expectationValidator->assertIsPrepared($this->prepared);
        }
    }

    /**
     * @return void
     */
    public function assertIsNotPrepared()
    {
        if (! is_null($this->prepared)) {
            $this->expectationValidator->assertIsNotPrepared($this->prepared);
        }
    }
}
