<?php

namespace Tests\Xala\EloquentMock;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use RuntimeException;

class InsertBuilderTest extends TestCase
{
    #[Test]
    public function itShouldValidateQuery(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('insert into "users" ("name") values (?)')
            ->withAnyBindings();

        $result = (new Builder($connection))
            ->from('users')
            ->insert([
                ['name' => 'xala']
            ]);

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldThrowExceptionWhenUnexpectedQuery(): void
    {
        $connection = $this->getFakeConnection();

        $builder = (new Builder($connection))
            ->from('users');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected insert query: [insert into "users" ("name") values (?)] [xala]');

        $builder->insert([
            ['name' => 'xala'],
        ]);
    }

    #[Test]
    public function itShouldThrowExceptionWhenQueryDoesNotMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('insert into "users" ("name") values (?)')
            ->withBindings(['xala']);

        $builder = (new Builder($connection))
            ->from('posts');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected insert query: [insert into "posts" ("title") values (?)] [PHP]');

        $builder->insert([
            ['title' => 'PHP']
        ]);
    }

    #[Test]
    public function itShouldValidateQueryWithMultipleRows(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('insert into "users" ("name") values (?), (?)')
            ->withBindings(['john', 'jane']);

        $result = (new Builder($connection))
            ->from('users')
            ->insert([
                ['name' => 'john'],
                ['name' => 'jane'],
            ]);

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldValidateBindings(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('insert into "users" ("name") values (?)')
            ->withBindings(['xala']);

        $result = (new Builder($connection))
            ->from('users')
            ->insert([
                ['name' => 'xala'],
            ]);

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldThrowExceptionWhenBindingsDontMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('insert into "users" ("name") values (?)')
            ->withBindings(['john']);

        $builder = (new Builder($connection))
            ->from('users');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected insert query bindings: [insert into "users" ("name") values (?)] [xala]');

        $builder->insert([
            ['name' => 'xala'],
        ]);
    }

    #[Test]
    public function itShouldReturnCorrectResult(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('insert into "users" ("name") values (?)')
            ->withBindings(['xala'])
            ->asFailedStatement();

        $result = (new Builder($connection))
            ->from('users')
            ->insert(['name' => 'xala']);

        static::assertFalse($result);
    }
}
