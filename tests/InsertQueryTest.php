<?php

namespace Tests\Xala\Elomock;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;

class InsertQueryTest extends TestCase
{
    #[Test]
    public function itShouldValidateQuery(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('insert into "users" ("name") values (?)');

        $result = (new Builder($connection))
            ->from('users')
            ->insert([
                ['name' => 'xala']
            ]);

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldFailWhenUnexpectedQuery(): void
    {
        $connection = $this->getFakeConnection();

        $builder = (new Builder($connection))
            ->from('users');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query: [insert into "users" ("name") values (?)] [xala]');

        $builder->insert([
            ['name' => 'xala'],
        ]);
    }

    #[Test]
    public function itShouldFailWhenQueryDoesNotMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('insert into "users" ("name") values (?)')
            ->withBindings(['xala']);

        $builder = (new Builder($connection))
            ->from('posts');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query: [insert into "posts" ("title") values (?)] [PHP]');

        $builder->insert([
            ['title' => 'PHP']
        ]);
    }

    #[Test]
    public function itShouldValidateQueryWithMultipleRows(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('insert into "users" ("name") values (?), (?)')
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

        $connection->expectQuery('insert into "users" ("name") values (?)', ['xala']);

        $result = (new Builder($connection))
            ->from('users')
            ->insert([
                ['name' => 'xala'],
            ]);

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldProvideBindingsUsingHelper(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('insert into "users" ("name") values (?)')
            ->withBindings(['xala']);

        $result = (new Builder($connection))
            ->from('users')
            ->insert([
                ['name' => 'xala'],
            ]);

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldFailWhenBindingsDontMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('insert into "users" ("name") values (?)', ['john']);

        $builder = (new Builder($connection))
            ->from('users');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query bindings: [insert into "users" ("name") values (?)] [xala]');

        $builder->insert([
            ['name' => 'xala'],
        ]);
    }

    #[Test]
    public function itShouldReturnCorrectResult(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('insert into "users" ("name") values (?)')
            ->withBindings(['xala'])
            ->asFailedStatement();

        $result = (new Builder($connection))
            ->from('users')
            ->insert(['name' => 'xala']);

        static::assertFalse($result);
    }

    #[Test]
    public function itShouldVerifyNamedBindings(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('insert into "users" ("name", "email") values (:name, :email)')
            ->withBindings([
                'name' => 'xala',
                'email' => 'xala@mail.com',
            ]);

        $connection->insert('insert into "users" ("name", "email") values (:name, :email)', [
            'name' => 'xala',
            'email' => 'xala@mail.com',
        ]);

        $connection->assertExpectationsFulfilled();
    }
}
