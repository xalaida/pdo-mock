<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;
use Tests\Xala\Elomock\Support\FakeConnectionResolver;
use Tests\Xala\Elomock\Support\User;

class ModelTest extends TestCase
{
    #[Test]
    public function itShouldSaveModelCorrectly(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('insert into "users" ("name") values (?)')
            ->withBindings(['xala']);

        $resolver = new FakeConnectionResolver($connection);

        User::setConnectionResolver($resolver);

        $user = new User(['name' => 'xala']);

        $result = $user->save(['touch' => false]);

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldSaveModelAndAssignIdCorrectly(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('insert into "users" ("name") values (?)')
            ->withBindings(['xala'])
            ->withLastInsertId(7);

        $resolver = new FakeConnectionResolver($connection);

        User::setConnectionResolver($resolver);

        $user = new User(['name' => 'xala']);
        $user->save(['touch' => false]);

        static::assertEquals(7, $user->getKey());
    }

    #[Test]
    public function itShouldSaveMultipleModelsAndAssignIdCorrectly(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('insert into "users" ("name") values (?)')
            ->withBindings(['john'])
            ->withLastInsertId(10);

        $connection->expectQuery('insert into "users" ("name") values (?)')
            ->withBindings(['jane'])
            ->withLastInsertId(11);

        $resolver = new FakeConnectionResolver($connection);

        User::setConnectionResolver($resolver);

        $john = new User(['name' => 'john']);
        $john->save(['touch' => false]);

        $jane = new User(['name' => 'jane']);
        $jane->save(['touch' => false]);

        static::assertEquals(10, $john->getKey());
        static::assertEquals(11, $jane->getKey());
    }

    #[Test]
    public function itShouldReturnModel(): void
    {
        $connection = $this->getFakeConnection();

        $john = new User(['id' => 7, 'name' => 'john']);

        $connection->expectQuery('select * from "users" where "id" = ? limit 1')
            ->withBindings([7])
            ->andReturnRow($john);

        $result = $connection
            ->table('users')
            ->find(7);

        static::assertEquals(7, $result->id);
        static::assertEquals('john', $result->name);
    }
}
