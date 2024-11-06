<?php

namespace Xalaida\PDOMock;

class QueryMatcherExact implements QueryMatcherInterface
{
    public function match($expectation, $reality)
    {
        return $expectation === $reality;
    }
}
