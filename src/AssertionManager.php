<?php

namespace Xalaida\PDOMock;

interface AssertionManager
{
    /**
     * @param int $count
     * @return void
     */
    public function incrementAssertions($count = 1);
}
