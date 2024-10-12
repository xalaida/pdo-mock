<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Xala\Elomock\FakeConnection;

class TestCase extends BaseTestCase
{
    protected function getFakeConnection(): FakeConnection
    {
        return new FakeConnection();
    }
}
