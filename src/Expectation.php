<?php

namespace Xalaida\PDOMock;

use Closure;
use InvalidArgumentException;
use PDO;
use PDOException;

class Expectation
{
    /**
     * @var PDO
     */
    public $pdo;

    /**
     * @var PDOStatementMock|null
     */
    public $statement = null;

    /**
     * @var string
     */
    public $query;

    /**
     * @var array|Closure|null
     */
    public $params = null;

    /**
     * @var bool|null
     */
    public $prepared = null;

    /**
     * @var int
     */
    public $rowCount = 0;

    /**
     * @var ResultSet|null
     */
    public $resultSet = null;

    /**
     * @var string|null
     */
    public $insertId = null;

    /**
     * @var PDOException|null
     */
    public $exceptionOnExecute = null;

    /**
     * @var PDOException|null
     */
    public $exceptionOnPrepare = null;

    /**
     * @param PDO $pdo
     * @param string $query
     */
    public function __construct($pdo, $query)
    {
        $this->pdo = $pdo;
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
     * @param array|Closure $params
     * @param bool $useParamValueType
     * @return $this
     */
    public function with($params, $useParamValueType = false)
    {
        if (is_callable($params)) {
            $this->params = $params;

            return $this;
        }

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
     * @param ResultSet $resultSet
     * @return $this
     */
    public function andFetch($resultSet)
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
        return $this->andFetch(
            ResultSet::fromArray($rows)
        );
    }

    /**
     * @param array $row
     * @return $this
     */
    public function andFetchRecord($row)
    {
        return $this->andFetch(
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
     * @return PDO
     */
    public function then()
    {
        return $this->pdo;
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
}
