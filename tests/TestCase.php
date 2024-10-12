<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Xala\Elomock\FakeConnection;
use Xala\Elomock\FakeLastInsertIdGenerator;
use Xala\Elomock\FakePdo;

class TestCase extends BaseTestCase
{
    protected function getFakeConnection(): FakeConnection
    {
        $pdo = new FakePdo(new FakeLastInsertIdGenerator());

        $connection = new FakeConnection($pdo);

        return $connection;
    }
}
