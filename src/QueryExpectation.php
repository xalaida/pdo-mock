<?php

namespace Xalaida\PDOMock;

use Closure;
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
     * @var ParamComparatorInterface
     */
    public $paramComparator;

    /**
     * @var string
     */
    public $query;

    /**
     * @var array<int|string, mixed>|Closure|null
     */
    public $params;

    /**
     * @var array<int|string, int>
     */
    public $types;

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
        $this->paramComparator = new ParamComparatorLoose();
        $this->query = $query;
    }

    /**
     * @param QueryComparatorInterface $queryComparator
     * @return self
     */
    public function usingQueryComparator($queryComparator)
    {
        $this->queryComparator = $queryComparator;

        return $this;
    }

    /**
     * @return self
     */
    public function toMatchRegex()
    {
        $this->queryComparator = new QueryComparatorRegex();

        return $this;
    }

    /**
     * @return self
     */
    public function toBeExact()
    {
        $this->queryComparator = new QueryComparatorExact();

        return $this;
    }

    /**
     * @param ParamComparatorInterface $paramComparator
     * @return self
     */
    public function usingParamComparator($paramComparator)
    {
        $this->paramComparator = $paramComparator;

        return $this;
    }

    /**
     * @return self
     */
    public function toMatchParamsStrictly()
    {
        $this->paramComparator = new ParamComparatorStrict();

        return $this;
    }

    /**
     * @return self
     */
    public function toMatchParamsLoosely()
    {
        $this->paramComparator = new ParamComparatorLoose();

        return $this;
    }

    /**
     * @return self
     */
    public function toMatchParamsNaturally()
    {
        $this->paramComparator = new ParamComparatorNatural();

        return $this;
    }

    /**
     * @param bool $prepared
     * @return self
     */
    public function toBePrepared($prepared = true)
    {
        $this->prepared = $prepared;

        return $this;
    }

    /**
     * @param array<int|string, int|string>|Closure $params
     * @param array<int|string, int> $types
     * @return self
     */
    public function with($params, $types = [])
    {
        if (is_callable($params)) {
            return $this->withParamsUsing($params);
        }

        $this->withParams($params, $types);

        return $this;
    }

    /**
     * @param string|int $param
     * @param mixed $value
     * @param int $type
     * @return self
     */
    public function withParam($param, $value, $type = null)
    {
        $this->params[$param] = $value;
        $this->types[$param] = $type;

        return $this;
    }

    /**
     * @param array<int|string, int|string> $params
     * @param array<int|string, int> $types
     * @return self
     */
    public function withParams($params, $types = [])
    {
        foreach ($params as $key => $value) {
            $param = is_int($key)
                ? $key + 1
                : $key;

            $this->withParam($param, $value);
        }

        $this->withTypes($types);

        return $this;
    }

    /**
     * @param array<int|string, int> $types
     * @return self
     */
    public function withTypes($types)
    {
        foreach ($types as $key => $type) {
            $param = is_int($key)
                ? $key + 1
                : $key;

            $this->types[$param] = $type;
        }

        return $this;
    }

    /**
     * @param callable $callback
     * @return self
     */
    public function withParamsUsing($callback)
    {
        $this->params = $callback;

        return $this;
    }

    /**
     * @param int|string $insertId
     * @return self
     */
    public function withInsertId($insertId)
    {
        $this->insertId = (string) $insertId;

        return $this;
    }

    /**
     * @param int $rowCount
     * @return self
     */
    public function affecting($rowCount)
    {
        $this->rowCount = $rowCount;

        return $this;
    }

    /**
     * @param ResultSet|array<array<int|string, int|string>> $resultSet
     * @return self
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
     * @return self
     */
    public function andFetchResultSet($resultSet)
    {
        $this->resultSet = $resultSet;

        return $this;
    }

    /**
     * @param array<array<int|string, int|string>> $rows
     * @return self
     */
    public function andFetchRows($rows)
    {
        return $this->andFetchResultSet(
            ResultSet::fromArray($rows)
        );
    }

    /**
     * @param array<int|string, int|string> $row
     * @return self
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
     * @return self
     */
    public function andFailOnExecute($exception)
    {
        $this->exceptionOnExecute = $exception;

        return $this;
    }

    /**
     * @param PDOException $exception
     * @return self
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
     * @param string $query
     * @return bool
     */
    public function compareQuery($query)
    {
        return $this->queryComparator->compare($this->query, $query);
    }

    /**
     * @param array<int|string, mixed> $params
     * @param array<int|string, int> $types
     * @return void
     */
    public function assertParamsMatch($params, $types)
    {
        if (! is_null($this->params)) {
            $this->expectationValidator->assertParamsMatch($this, $params, $types);
        }
    }

    /**
     * @param array<int|string, mixed> $params
     * @param array<int|string, int> $types
     * @return bool
     */
    public function compareParams($params, $types)
    {
        if (is_callable($this->params)) {
            return call_user_func($this->params, $params, $types) !== false;
        }

        $expectation = [];

        foreach ($this->params as $param => $value) {
            $expectation[$param]['value'] = $value;
            $expectation[$param]['type'] = isset($this->types[$param])
                ? $this->types[$param]
                : null;
        }

        $reality = [];

        foreach ($params as $param => $value) {
            $reality[$param]['value'] = $value;
            $reality[$param]['type'] = $types[$param];
        }

        return $this->paramComparator->compare($expectation, $reality);
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
