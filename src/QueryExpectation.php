<?php

namespace Xalaida\PDOMock;

use Closure;
use InvalidArgumentException;
use PDO;
use PDOException;

class QueryExpectation
{
    /**
     * @var AssertionValidatorInterface
     */
    public $assertionValidator;

    /**
     * @var string
     */
    public $query;

    /**
     * @var array|Closure|null
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
     * @param AssertionValidatorInterface $assertionValidator
     * @param string $query
     */
    public function __construct($assertionValidator, $query)
    {
        $this->assertionValidator = $assertionValidator;
        $this->query = $query;
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
     * @param array|Closure $params
     * @param bool $useParamValueType
     * @return $this
     */
    public function with($params, $useParamValueType = false)
    {
        if (is_callable($params)) {
            return $this->withParamsUsing($params);
        }

        if (is_array($params)) {
            return $this->withParams($params, $useParamValueType);
        }

        throw new InvalidArgumentException('Unsupported $params type.');
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
     * @param array $params
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
     * @param array $types
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
     * @param string $insertId
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
     * @param ResultSet|array $resultSet
     * @return $this
     */
    public function andFetch($resultSet)
    {
        if ($resultSet instanceof ResultSet) {
            return $this->andFetchResultSet($resultSet);
        }

        if (is_array($resultSet)) {
            return $this->andFetchRows($resultSet);
        }

        throw new InvalidArgumentException('Unsupported $resultSet type.');
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
     * @param array $rows
     * @return $this
     */
    public function andFetchRows($rows)
    {
        return $this->andFetchResultSet(
            ResultSet::fromArray($rows)
        );
    }

    /**
     * @param array $row
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
        $this->assertionValidator->assertQueryMatch($this->query, $query);
    }

    /**
     * @param array $params
     * @return void
     */
    public function assertParamsMatch($params)
    {
        if (! is_null($this->params)) {
            $this->assertionValidator->assertParamsMatch($this->params, $params);
        }
    }

    /**
     * @return void
     */
    public function assertIsPrepared()
    {
        if (! is_null($this->prepared)) {
            $this->assertionValidator->assertIsPrepared($this->prepared);
        }
    }

    /**
     * @return void
     */
    public function assertIsNotPrepared()
    {
        if (! is_null($this->prepared)) {
            $this->assertionValidator->assertIsNotPrepared($this->prepared);
        }
    }
}
