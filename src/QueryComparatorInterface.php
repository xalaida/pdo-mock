<?php

namespace Xalaida\PDOMock;

interface QueryComparatorInterface
{
    /**
     * @param string $expectation
     * @param string $reality
     * @return bool
     */
    public function compare($expectation, $reality);
}
