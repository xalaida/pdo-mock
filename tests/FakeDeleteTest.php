<?php

namespace Tests\Xala\EloquentMock;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Xala\EloquentMock\FakeConnection;

class FakeDeleteTest extends TestCase
{
    #[Test]
    public function itShouldVerifyQuery(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('delete from "users" where ("id" = ?)')
            ->withAnyBindings();

        $result = (new Builder($connection))
            ->from('users')
            ->where(['id' => 7])
            ->delete();

        static::assertEquals(1, $result);
    }

    #[Test]
    public function itShouldThrowExceptionWhenUnexpectedQuery(): void
    {
        $connection = $this->getFakeConnection();

        $builder = (new Builder($connection))
            ->from('users')
            ->where(['id' => 7]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unexpected delete query: [delete from "users" where ("id" = ?)] [7]');

        $builder->delete();
    }

    #[Test]
    public function itShouldVerifyBindings(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('delete from "users" where ("id" = ?)')
            ->withBindings([7]);

        $result = (new Builder($connection))
            ->from('users')
            ->where(['id' => 7])
            ->delete();

        static::assertEquals(1, $result);
    }

    #[Test]
    public function itShouldThrowExceptionWhenBindingsDontMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('delete from "users" where ("id" = ?)')
            ->withBindings([1]);

        $builder = (new Builder($connection))
            ->from('users')
            ->where(['id' => 7]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unexpected delete query bindings: [delete from "users" where ("id" = ?)] [7]');

        $builder->delete();
    }

    #[Test]
    public function itShouldReturnAffectedRows(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('delete from "products"')
            ->andAffectRows(3);

        $result = (new Builder($connection))
            ->from('products')
            ->delete();

        static::assertEquals(3, $result);
    }

    #[Test]
    public function itShouldThrowExceptionWhenQueryWasntExecuted(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('delete from "users" where "id" = ?')
            ->withBindings([1]);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage("Some queries were not executed: 1\nFailed asserting that an array is empty.");

        $connection->assertExpectedQueriesExecuted();
    }

    protected function getFakeConnection(): FakeConnection
    {
        return new FakeConnection();
    }
}
