<?php

namespace Tests\Xala\Elomock;

use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use Xala\Elomock\PDOMock;

class ExecuteTest extends TestCase
{
    #[Test]
    public function itShouldExecuteQuery(): void
    {
        $scenario = function (PDO $pdo) {
            $result = $pdo->exec('select * from "books"');

            static::assertSame(0, $result);
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();
        $mock->expect('select * from "books"');
        $scenario($mock);
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
