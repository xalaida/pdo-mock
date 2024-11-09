<?php

namespace Xalaida\PDOMock;

class ResultSet
{
    /**
     * @var array
     */
    public $cols = [];

    /**
     * @var array
     */
    public $rows = [];

    /**
     * @param array $results
     * @return self
     */
    public static function fromArray($results)
    {
        $resultSet = new self();

        $resultSet->setCols(array_keys($results[0]));

        foreach ($results as $result) {
            $resultSet->addRow(array_values($result));
        }

        return $resultSet;
    }

    /**
     * @param array $cols
     * @return $this
     */
    public function setCols($cols)
    {
        $this->cols = $cols;

        return $this;
    }

    /**
     * @param array $rows
     * @return $this
     */
    public function setRows($rows)
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * @param array $row
     * @return $this
     */
    public function addRow($row)
    {
        $this->rows[] = $row;

        return $this;
    }
}
