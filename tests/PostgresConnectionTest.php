<?php

namespace Tests\Xala\Elomock;

use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Xala\Elomock\FakeConnection;

class PostgresConnectionTest extends TestCase
{
    #[Test]
    public function itShouldUsePostgresGrammar(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from "users" where "name"::text ilike ?')
            ->withBindings(['%john%'])
            ->andReturnRows([
                ['id' => 1, 'name' => 'John'],
            ]);

        $result = $connection
            ->table('users')
            ->where('name', 'ilike', '%john%')
            ->get();

        static::assertInstanceOf(Collection::class, $result);
        static::assertCount(1, $result);
        static::assertEquals(1, $result[0]->id);
        static::assertEquals('John', $result[0]->name);
    }

    #[Test]
    public function itShouldUseLastInsertIdCorrectly(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('insert into "users" ("name") values (?) returning "id"')
            ->withBindings(['John'])
            ->withLastInsertId(777);

        $id = $connection
            ->table('users')
            ->insertGetId([
                'name' => 'John',
            ]);

        static::assertEquals(777, $id);
    }

    protected function getFakeConnection(): FakeConnection
    {
        $connection = new FakeConnection();
        $connection->setQueryGrammar(new PostgresGrammar());

        return $connection;
    }
}
