<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;

class FakeInsertIdGeneratorTest extends TestCase
{
    #[Test]
    public function itShouldGenerateLastInsertId(): void
    {
        $connection = $this->getFakeConnection();

        $connection->insertIdGenerator->lastInsertId = 10;

        static::assertEquals(10, $connection->getLastInsertId());
        static::assertEquals(11, $connection->getLastInsertId());
        static::assertEquals(12, $connection->getLastInsertId());
    }

    #[Test]
    public function itShouldUseManuallySpecifiedLastInsertId(): void
    {
        $connection = $this->getFakeConnection();

        $connection->lastInsertId = 7;

        static::assertEquals(7, $connection->getLastInsertId());
        static::assertEquals(1, $connection->getLastInsertId());
        static::assertEquals(2, $connection->getLastInsertId());
    }
}
