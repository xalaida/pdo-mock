<?php

namespace Xalaida\PDOMock;

class ParamsComparator
{
    /**
     * @param array<int|string, array{value: mixed, type: int}>|callable $expectation
     * @param array<int|string, array{value: mixed, type: int}> $reality
     * @return bool
     */
    public function compare($expectation, $reality)
    {
        if (is_callable($expectation)) {
            return $this->compareUsingCallback($expectation, $reality);
        }

        return $expectation == $reality;
    }

    /**
     * @param callable $expectation
     * @param array<int|string, array{value: mixed, type: int}> $reality
     * @return bool
     */
    protected function compareUsingCallback($expectation, $reality)
    {
        return call_user_func($expectation, $reality) !== false;
    }
}
