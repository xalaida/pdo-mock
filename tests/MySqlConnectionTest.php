<?php

namespace Tests\Xala\Elomock;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Xala\Elomock\FakeConnection;

class MySqlConnectionTest extends TestCase
{
    #[Test]
    public function itShouldUseMySqlGrammar(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('select * from `users` where `name` like ?')
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

        $connection->shouldQuery('insert into `users` (`name`) values (?)')
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
        $connection->setQueryGrammar(new MySqlGrammar());

        return $connection;
    }
}
