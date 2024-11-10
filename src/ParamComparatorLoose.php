<?php

namespace Xalaida\PDOMock;

class ParamComparatorLoose implements ParamComparatorInterface
{
    /**
     * @param array<int|string, array{value: mixed, type: int}> $expectation
     * @param array<int|string, array{value: mixed, type: int}> $reality
     * @return bool
     */
    public function compare($expectation, $reality)
    {
        if (count($expectation) !== count($reality)) {
            return false;
        }

        foreach ($expectation as $key => $expectedParam) {
            if (! array_key_exists($key, $reality)) {
                return false;
            }

            $actualParam = $reality[$key];

            if ($expectedParam['value'] != $actualParam['value']) {
                return false;
            }
        }

        return true;
    }
}
