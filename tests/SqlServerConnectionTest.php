<?php

namespace Tests\Xala\Elomock;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\SqlServerGrammar;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Xala\Elomock\FakeConnection;

class SqlServerConnectionTest extends TestCase
{
    #[Test]
    public function itShouldUseSqlServerGrammar(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('select * from [users] where [name] like ?')
            ->withBindings(['%john%'])
            ->andReturnRows([
                ['id' => 777, 'name' => 'John'],
            ]);

        $result = (new Builder($connection))
            ->from('users')
            ->whereLike('name', '%john%')
            ->get();

        static::assertInstanceOf(Collection::class, $result);
        static::assertCount(1, $result);
        static::assertEquals(777, $result[0]['id']);
        static::assertEquals('John', $result[0]['name']);
    }

    #[Test]
    public function itShouldUseLastInsertIdCorrectly(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('insert into [users] ([name]) values (?)')
            ->withBindings(['John'])
            ->withLastInsertId(777);

        $id = (new Builder($connection))
            ->from('users')
            ->insertGetId([
                'name' => 'John',
            ]);

        static::assertEquals(777, $id);
    }

    protected function getFakeConnection(): FakeConnection
    {
        $connection = new FakeConnection();
        $connection->setQueryGrammar(new SqlServerGrammar());

        return $connection;
    }
}