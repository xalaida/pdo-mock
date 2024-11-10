<?php

namespace Xalaida\PDOMock;

class QueryComparatorExact implements QueryComparatorInterface
{
    /**
     * @inheritDoc
     */
    public function compare($expectation, $reality)
    {
        return $expectation === $reality;
    }
}
