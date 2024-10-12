<?php

namespace Tests\Xala\EloquentMock;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Database\Query\Processors\PostgresProcessor;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Xala\EloquentMock\FakeConnection;
use Xala\EloquentMock\FakeLastInsertIdGenerator;
use Xala\EloquentMock\FakePdo;

class PostgresFakeConnectionTest extends TestCase
{
    // TODO: test integration with other libraries that provides enhanced postgres support

    #[Test]
    public function itShouldUsePostgresGrammar(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('select * from "users" where "name"::text ilike ?')
            ->withBindings(['%john%'])
            ->andReturnRows([
                ['id' => 1, 'name' => 'John'],
            ]);

        $result = (new Builder($connection))
            ->from('users')
            ->whereLike('name', '%john%')
            ->get();

        static::assertInstanceOf(Collection::class, $result);
        static::assertCount(1, $result);
        static::assertEquals(1, $result[0]['id']);
        static::assertEquals('John', $result[0]['name']);
    }

    #[Test]
    public function itShouldUsePostgresProcessor(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('insert into "users" ("name") values (?) returning "id"')
            ->withBindings(['John'])
            ->andReturnRows([
                ['id' => 777],
            ]);

        $id = (new Builder($connection))
            ->from('users')
            ->insertGetId([
                'name' => 'John',
            ]);

        static::assertEquals(777, $id);
    }

    protected function getFakeConnection(): FakeConnection
    {
        $pdo = new FakePdo(new FakeLastInsertIdGenerator());
        $connection = new FakeConnection($pdo);
        $connection->setQueryGrammar(new PostgresGrammar());
        $connection->setPostProcessor(new PostgresProcessor());

        return $connection;
    }
}
