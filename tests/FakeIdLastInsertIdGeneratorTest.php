<?php

namespace Tests\Xala\EloquentMock;

use PHPUnit\Framework\Attributes\Test;

class FakeIdLastInsertIdGeneratorTest extends TestCase
{
    #[Test]
    public function itShouldGenerateLastInsertId(): void
    {
        $connection = $this->getFakeConnection();

        $connection->getPdo()->lastInsertIdGenerator->lastInsertId = 10;

        static::assertEquals('10', $connection->getPdo()->lastInsertId());
        static::assertEquals('11', $connection->getPdo()->lastInsertId());
        static::assertEquals('12', $connection->getPdo()->lastInsertId());
    }

    #[Test]
    public function itShouldUseManuallySpecifiedLastInsertId(): void
    {
        $connection = $this->getFakeConnection();

        $connection->getPdo()->lastInsertId = '7';

        static::assertEquals('7', $connection->getPdo()->lastInsertId());
        static::assertEquals('1', $connection->getPdo()->lastInsertId());
        static::assertEquals('2', $connection->getPdo()->lastInsertId());
    }

    #[Test]
    public function itShouldReturnFalseWhenNoGeneratorProvided(): void
    {
        $connection = $this->getFakeConnection();

        $connection->getPdo()->lastInsertIdGenerator = null;

        static::assertFalse(false, $connection->getPdo()->lastInsertId());

        $connection->getPdo()->lastInsertId = '12';

        static::assertEquals('12', $connection->getPdo()->lastInsertId());
        static::assertFalse(false, $connection->getPdo()->lastInsertId());
    }
}
