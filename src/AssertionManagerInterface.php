<?php

namespace Xalaida\PDOMock;

interface AssertionManagerInterface
{
    /**
     * @param int $count
     * @return void
     */
    public function increment($count = 1);
}
