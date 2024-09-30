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
        // TODO: configure connection properly
        // TODO: support expending specific connection types (pgsql, mysql)
//        parent::__construct(new FakePdo(), 'dbname', []);

        return new FakeConnection(new FakePdo(new FakeLastInsertIdGenerator()));
    }
}
