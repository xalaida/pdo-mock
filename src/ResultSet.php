<?php

namespace Xala\Elomock;

use Generator;

class ResultSet
{
    public array $cols = [];

    public array $rows = [];

    public static function fromRecords(array $records): static
    {
        $resultSet = new static();

        $resultSet->setCols(array_keys($records[0]));

        foreach ($records as $record) {
            $resultSet->addRow(array_values($record));
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
