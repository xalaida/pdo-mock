<?php

namespace Tests\Xala\EloquentMock;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Xala\EloquentMock\FakeConnection;
use Xala\EloquentMock\FakeLastInsertIdGenerator;
use Xala\EloquentMock\FakePdo;

class TestCase extends BaseTestCase
{
    protected function getFakeConnection(): FakeConnection
    {
        return new FakeConnection(new FakePdo(new FakeLastInsertIdGenerator()));
    }
}
