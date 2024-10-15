<?php

namespace Tests\Xala\Elomock;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;

class UpdateQueryTest extends TestCase
{
    #[Test]
    public function itShouldVerifyQuery(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('update "users" set "name" = ? where ("id" = ?)');

        $result = (new Builder($connection))
            ->from('users')
            ->where(['id' => 1])
            ->update(['name' => 'xala']);

        static::assertEquals(1, $result);
    }

    #[Test]
    public function itShouldFailWhenUnexpectedQuery(): void
    {
        $connection = $this->getFakeConnection();

        $builder = (new Builder($connection))
            ->from('users')
            ->where(['id' => 1]);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query: [update "users" set "name" = ? where ("id" = ?)] [xala, 1]');

        $builder->update(['name' => 'xala']);
    }

    #[Test]
    public function itShouldVerifyBindings(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('update "users" set "name" = ? where ("id" = ?)')
            ->withBindings(['xala', 1]);

        $result = (new Builder($connection))
            ->from('users')
            ->where(['id' => 1])
            ->update(['name' => 'xala']);

        static::assertEquals(1, $result);
    }

    #[Test]
    public function itShouldFailWhenBindingsDontMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('update "users" set "name" = ? where ("id" = ?)')
            ->withBindings(['john', 1]);

        $builder = (new Builder($connection))
            ->from('users')
            ->where(['id' => 1]);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query bindings: [update "users" set "name" = ? where ("id" = ?)] [xala, 1]');

        $builder->update(['name' => 'xala']);
    }

    #[Test]
    public function itShouldReturnAffectedRows(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('update "products" set "status" = ?')
            ->withBindings(['processed'])
            ->andAffectRows(3);

        $result = (new Builder($connection))
            ->from('products')
            ->update(['status' => 'processed']);

        static::assertEquals(3, $result);
    }
}
