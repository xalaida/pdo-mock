<?php

namespace Tests\Xala\EloquentMock;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Xala\EloquentMock\Support\FakeConnectionResolver;
use Tests\Xala\EloquentMock\Support\User;
use Xala\EloquentMock\FakeConnection;

class ModelTest extends TestCase
{
    #[Test]
    public function itShouldSaveModelCorrectly(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('insert into "users" ("name") values (?)')
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

        $connection->shouldQuery('insert into "users" ("name") values (?)')
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

        $connection->shouldQuery('insert into "users" ("name") values (?)')
            ->withBindings(['john'])
            ->withLastInsertId(10);

        $connection->shouldQuery('insert into "users" ("name") values (?)')
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

    protected function getFakeConnection(): FakeConnection
    {
        return new FakeConnection();
    }
}
