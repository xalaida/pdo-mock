<?php

namespace Tests\Xala\Elomock;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;

class SkipUpdateQueryTest extends TestCase
{
    #[Test]
    public function itShouldVerifySkippedQuery(): void
    {
        $connection = $this->getFakeConnection();

        $connection->skipWriteQueries();

        $result = (new Builder($connection))
            ->from('users')
            ->where(['id' => 7])
            ->update(['name' => 'xala']);

        static::assertEquals(1, $result);

        $connection->assertQueried('update "users" set "name" = ? where ("id" = ?)', ['xala', 7]);
    }

    #[Test]
    public function itShouldThrowExceptionWhenQueryWasntExecuted(): void
    {
        $connection = $this->getFakeConnection();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage("No queries were executed");

        $connection->assertQueried('update "users" set "name" = ? where ("id" = ?)', [7]);
    }

    #[Test]
    public function itShouldThrowExceptionWhenQueryDoesntMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->skipWriteQueries();

        $result = (new Builder($connection))
            ->from('users')
            ->where(['id' => 7])
            ->update(['name' => 'xala']);

        static::assertEquals(1, $result);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Query does not match');

        $connection->assertQueried('update "posts" set "name" = ? where ("id" = ?)', ['xala', 7]);
    }

    #[Test]
    public function itShouldThrowExceptionWhenBindingsDontMatch(): void
    {
        $connection = $this->getFakeConnection();

        $connection->skipWriteQueries();

        $result = (new Builder($connection))
            ->from('users')
            ->where(['id' => 7])
            ->update(['name' => 'xala']);

        static::assertEquals(1, $result);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Bindings do not match');

        $connection->assertQueried('update "users" set "name" = ? where ("id" = ?)', ['xala', 5]);
    }
}
