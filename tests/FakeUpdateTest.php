<?php

namespace Tests\Xala\EloquentMock;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Xala\EloquentMock\FakeConnection;

class FakeUpdateTest extends TestCase
{
    #[Test]
    public function itShouldVerifyQuery(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('update "users" set "name" = ? where ("id" = ?)')
            ->withAnyBindings();

        $result = (new Builder($connection))
            ->from('users')
            ->where(['id' => 1])
            ->update(['name' => 'xala']);

        static::assertEquals(1, $result);
    }

    #[Test]
    public function itShouldThrowExceptionWhenUnexpectedQuery(): void
    {
        $connection = $this->getFakeConnection();

        $builder = (new Builder($connection))
            ->from('users')
            ->where(['id' => 1]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unexpected update query: [update "users" set "name" = ? where ("id" = ?)] [xala, 1]');

        $builder->update(['name' => 'xala']);
    }

    #[Test]
    public function itShouldVerifyBindings(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('update "users" set "name" = ? where ("id" = ?)')
            ->withBindings(['xala', 1]);

        $result = (new Builder($connection))
            ->from('users')
            ->where(['id' => 1])
            ->update(['name' => 'xala']);

        static::assertEquals(1, $result);
    }

    #[Test]
    public function itShouldThrowExceptionWhenBindingsDontMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('update "users" set "name" = ? where ("id" = ?)')
            ->withBindings(['John', 1]);

        $builder = (new Builder($connection))
            ->from('users')
            ->where(['id' => 1]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unexpected update query bindings: [update "users" set "name" = ? where ("id" = ?)] [xala, 1]');

        $builder->update(['name' => 'xala']);
    }

    protected function getFakeConnection(): FakeConnection
    {
        return new FakeConnection();
    }
}
