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
        $pdo = new FakePdo(new FakeLastInsertIdGenerator());

        $connection = new FakeConnection($pdo);

        $pdo->setConnection($connection);

        return $connection;
    }
}
