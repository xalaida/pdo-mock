<?php

namespace Tests\Xala\Elomock;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;

class DeleteQueryTest extends TestCase
{
    #[Test]
    public function itShouldVerifyQuery(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('delete from "users" where ("id" = ?)');

        $result = (new Builder($connection))
            ->from('users')
            ->where(['id' => 7])
            ->delete();

        static::assertEquals(1, $result);
    }

    #[Test]
    public function itShouldFailWhenUnexpectedQuery(): void
    {
        $connection = $this->getFakeConnection();

        $builder = (new Builder($connection))
            ->from('users')
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

        $result = (new Builder($connection))
            ->from('users')
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

        $builder = (new Builder($connection))
            ->from('users')
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
            ->andAffectRows(3);

        $result = (new Builder($connection))
            ->from('products')
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
