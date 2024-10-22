<?php

namespace Xala\Elomock\Laravel;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\Processor;

class FakeProcessor extends Processor
{
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        $query->getConnection()->insert($sql, $values);

        return $query->getConnection()->getLastInsertId();
    }
}
