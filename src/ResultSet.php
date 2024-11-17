<?php

namespace Xalaida\PDOMock;

class ResultSet
{
    /**
     * @var array<int|string>
     */
    public $cols = [];

    /**
     * @var iterable<array<int|string>>
     */
    public $rows = [];

    /**
     * @param array<array<int|string, int|string>> $results
     * @return self
     */
    public static function fromAssociativeArray($results)
    {
        $cols = array_keys($results[0]);

        $rows = [];

        foreach ($results as $result) {
            $rows[] = array_values($result);
        }

        return (new self())
            ->setCols($cols)
            ->setRows($rows);
    }

    /**
     * @param array<int|string> $cols
     * @return self
     */
    public function setCols($cols)
    {
        $this->cols = $cols;

        return $this;
    }

    /**
     * @param iterable<array<int|string>> $rows
     * @return self
     */
    public function setRows($rows)
    {
        $this->rows = $rows;

        return $this;
    }
}
