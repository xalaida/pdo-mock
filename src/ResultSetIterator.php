<?php

namespace Xalaida\PDOMock;

use ArrayIterator;
use Iterator;

/**
 * @implements Iterator<int, array<int, mixed>>
 */
class ResultSetIterator implements Iterator
{
    /**
     * @var Iterator<int, array<int, mixed>>
     */
    protected $rows;

    /**
     * @var array<int|string>
     */
    protected $cols;

    /**
     * @param Iterator<int, array<int, mixed>> $rows
     * @param array<int|string> $cols
     */
    public function __construct($rows, $cols)
    {
        $this->cols = $cols;
        $this->rows = $rows;
    }

    /**
     * @param ResultSet$resultSet
     * @return self
     */
    public static function fromResultSet($resultSet)
    {
        return new self(
            $resultSet->rows instanceof Iterator
                ? $resultSet->rows
                : new ArrayIterator($resultSet->rows),
            $resultSet->cols
        );
    }

    /**
     * @return array<int, string>
     */
    public function cols()
    {
        return $this->cols;
    }

    /**
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        $this->rows->next();
    }

    /**
     * @return array<int, string>
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->rows->current();
    }

    /**
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->rows->key();
    }

    /**
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return $this->rows->valid();
    }

    /**
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->rows->rewind();
    }
}
