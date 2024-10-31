<?php

namespace Xalaida\PDOMock;

class AssertionManager implements AssertionManagerInterface
{
    public $count = 0;

    /**
     * @inheritDoc
     */
    public function increment($count = 1)
    {
        $this->count += $count;
    }
}
