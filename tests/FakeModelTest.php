<?php

namespace Tests\Xala\EloquentMock;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Xala\EloquentMock\FakeConnection;

class FakeModelTest extends TestCase
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
            ->withBindings(['John'])
            ->withLastInsertId(10);

        $connection->shouldQuery('insert into "users" ("name") values (?)')
            ->withBindings(['Jane'])
            ->withLastInsertId(11);

        $resolver = new FakeConnectionResolver($connection);

        User::setConnectionResolver($resolver);

        $john = new User(['name' => 'John']);
        $john->save(['touch' => false]);

        $jane = new User(['name' => 'Jane']);
        $jane->save(['touch' => false]);

        static::assertEquals(10, $john->getKey());
        static::assertEquals(11, $jane->getKey());
    }

    protected function getFakeConnection(): FakeConnection
    {
        return new FakeConnection();
    }
}
