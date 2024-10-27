<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Xala\Elomock\PDOMock;

class ExecuteTest extends TestCase
{
    #[Test]
    public function itShouldExecuteQuery(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"');

        $result = $pdo->exec('select * from "books"');

        static::assertSame(0, $result);
    }

    #[Test]
    public function itShouldCalculatesRowCountUsingFetchRows(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->andFetchRows([
                ['id' => 1],
                ['id' => 2],
                ['id' => 3],
            ]);

        $result = $pdo->exec('select * from "books"');

        static::assertSame(3, $result);
    }

    #[Test]
    public function itShouldFailOnUnexpectedQuery(): void
    {
        $pdo = new PDOMock();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query: select * from "books"');

        $pdo->exec('select * from "books"');
    }

    #[Test]
    public function itShouldFailWhenQueryDoesntMatch(): void
    {
        $pdo = new PDOMock();
        $pdo->expect('select * from "users"');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query: select * from "books"');

        $pdo->exec('select * from "books"');
    }

    #[Test]
    public function itShouldFailWhenQueryIsNotExecuted(): void
    {
        $pdo = new PDOMock();
        $pdo->expect('select * from "books"');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Some expectations were not fulfilled.');

        $pdo->assertExpectationsFulfilled();
    }
}
