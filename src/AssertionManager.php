<?php

namespace Xalaida\PDOMock;

interface AssertionManager
{
    /**
     * @param int $count
     * @return void
     */
    public function increment($count = 1);
}
