<?php

namespace Tests\Xala\Elomock;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Database\Query\Processors\PostgresProcessor;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Xala\Elomock\FakeConnection;
use Xala\Elomock\FakeLastInsertIdGenerator;
use Xala\Elomock\FakePdo;

class PostgresConnectionTest extends TestCase
{
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
