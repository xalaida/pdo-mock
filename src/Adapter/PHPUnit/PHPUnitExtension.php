<?php

namespace Xalaida\PDOMock\Adapter\PHPUnit;

use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use Xalaida\PDOMock\PDOMock;

class PHPUnitExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        PDOMock::useAdapter(new PHPUnitAdapter());
    }
}
