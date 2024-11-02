<?php

namespace Xalaida\PDOMock\Adapter\PHPUnit;

use PHPUnit\Runner\Extension\Extension as PHPUnitExtension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use Xalaida\PDOMock\ExpectationManager;

class Extension implements PHPUnitExtension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        ExpectationManager::useAssertionValidator(new AssertionValidator());
    }
}
