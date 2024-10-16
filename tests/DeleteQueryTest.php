<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;

class DeleteQueryTest extends TestCase
{
    #[Test]
    public function itShouldVerifyQuery(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('delete from "users" where ("id" = ?)');

        $result = $connection
            ->table('users')
            ->where(['id' => 7])
            ->delete();

        static::assertEquals(1, $result);
    }

    #[Test]
    public function itShouldFailWhenUnexpectedQuery(): void
    {
        $connection = $this->getFakeConnection();

        $builder = $connection
            ->table('users')
            ->where(['id' => 7]);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query: [delete from "users" where ("id" = ?)] [7]');

        $builder->delete();
    }

    #[Test]
    public function itShouldVerifyBindings(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('delete from "users" where ("id" = ?)')
            ->withBindings([7]);

        $result = $connection
            ->table('users')
            ->where(['id' => 7])
            ->delete();

        static::assertEquals(1, $result);
    }

    #[Test]
    public function itShouldFailWhenBindingsDontMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('delete from "users" where ("id" = ?)')
            ->withBindings([1]);

        $builder = $connection
            ->table('users')
            ->where(['id' => 7]);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query bindings: [delete from "users" where ("id" = ?)] [7]');

        $builder->delete();
    }

    #[Test]
    public function itShouldReturnAffectedRows(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('delete from "products"')
            ->andAffectCount(3);

        $result = $connection
            ->table('products')
            ->delete();

        static::assertEquals(3, $result);
    }

    #[Test]
    public function itShouldFailWhenQueryWasntExecuted(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('delete from "users" where "id" = ?')
            ->withBindings([1]);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Some expectations were not fulfilled.');

        $connection->assertExpectationsFulfilled();
    }
}
