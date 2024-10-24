<?php

namespace Tests\Xala\Elomock\Laravel;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Xala\Elomock\Laravel\FakeConnection;

class TestCase extends BaseTestCase
{
    protected function getFakeConnection(): FakeConnection
    {
        return new FakeConnection();
    }
}