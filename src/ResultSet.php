<?php

namespace Xala\Elomock;

class ResultSet
{
    public array $cols = [];

    public array $rows = [];

    public static function fromArray(array $results): static
    {
        $resultSet = new static();

        $resultSet->setCols(array_keys($results[0]));

        foreach ($results as $result) {
            $resultSet->addRow(array_values($result));
        }

        return $resultSet;
    }

    public function setCols(array $cols): static
    {
        $this->cols = $cols;

        return $this;
    }

    public function setRows(array $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    public function addRow(array $row): static
    {
        $this->rows[] = $row;

        return $this;
    }
}
