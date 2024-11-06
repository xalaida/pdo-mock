<?php

namespace Xalaida\PDOMock;

interface QueryMatcherInterface
{
    /**
     * @param string $expectation
     * @param string $reality
     * @return bool
     */
    public function match($expectation, $reality);
}
