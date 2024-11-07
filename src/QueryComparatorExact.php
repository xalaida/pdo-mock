<?php

namespace Xalaida\PDOMock;

class QueryComparatorExact implements QueryComparatorInterface
{
    public function compare($expectation, $reality)
    {
        return $expectation === $reality;
    }
}
