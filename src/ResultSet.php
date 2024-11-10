<?php

namespace Xalaida\PDOMock;

class ResultSet
{
    /**
     * @var array<int|string>
     */
    public $cols = [];

    /**
     * @var array<array<int|string>>
     */
    public $rows = [];

    /**
     * @param array<array<int|string, int|string>> $results
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
     * @param array<int|string> $cols
     * @return $this
     */
    public function setCols($cols)
    {
        $this->cols = $cols;

        return $this;
    }

    /**
     * @param array<array<int|string>> $rows
     * @return $this
     */
    public function setRows($rows)
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * @param array<int|string> $row
     * @return $this
     */
    public function addRow($row)
    {
        $this->rows[] = $row;

        return $this;
    }
}
