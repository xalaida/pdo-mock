<?php

namespace Xalaida\PDOMock;

interface ParamComparatorInterface
{
    /**
     * @param array<int|string, array{value: mixed, type: int}>|callable $expectation
     * @param array<int|string, array{value: mixed, type: int}> $reality
     * @return bool
     */
    public function compare($expectation, $reality);
}
