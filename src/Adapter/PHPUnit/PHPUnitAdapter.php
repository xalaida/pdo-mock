<?php

namespace Xalaida\PDOMock\Adapter\PHPUnit;

use Xalaida\PDOMock\AdapterInterface;
use Xalaida\PDOMock\PDOMock;

class PHPUnitAdapter implements AdapterInterface
{
    public function configure()
    {
        PDOMock::useExpectationValidator(new ExpectationValidator());
    }
}
