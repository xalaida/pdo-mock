<?php

namespace Tests\Xala\EloquentMock;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Xala\EloquentMock\FakeConnection;

class PostInsertBuilderTest extends TestCase
{
    #[Test]
    public function itShouldHandleQueriesOnFly(): void
    {
        $connection = $this->getFakeConnection();

        $connection->onInsertQuery(fn () => true);

        $result = (new Builder($connection))
            ->from('users')
            ->insert(['name' => 'john']);

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldVerifyExecutedQuery(): void
    {
        $connection = $this->getFakeConnection();

        $connection->onInsertQuery(fn () => true);

        $result = (new Builder($connection))
            ->from('users')
            ->insert(['name' => 'xala']);

        static::assertTrue($result);

        $connection->assertQueried('insert into "users" ("name") values (?)', ['xala']);
    }

    #[Test]
    public function itShouldThrowExceptionWhenQueryWasntExecuted(): void
    {
        $connection = $this->getFakeConnection();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage("No queries were executed");

        $connection->assertQueried('insert into "users" ("name") values (?)', ['xala']);
    }

    #[Test]
    public function itShouldThrowExceptionWhenQueryDoesntMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->onInsertQuery(fn () => true);

        $result = (new Builder($connection))
            ->from('users')
            ->insert(['name' => 'xala']);

        static::assertEquals(1, $result);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Query does not match');

        $connection->assertQueried('insert into "users" ("email") values (?)', ['xala']);
    }

    #[Test]
    public function itShouldThrowExceptionWhenBindingsDontMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->onInsertQuery(fn () => true);

        $result = (new Builder($connection))
            ->from('users')
            ->insert(['name' => 'xala']);

        static::assertEquals(1, $result);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Bindings do not match');

        $connection->assertQueried('insert into "users" ("name") values (?)', ['john']);
    }

    // TODO: provide last insert id somehow during execution

    protected function getFakeConnection(): FakeConnection
    {
        return new FakeConnection();
    }
}
