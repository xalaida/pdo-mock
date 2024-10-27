<?php

namespace Tests\Xala\Elomock;

use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use Xala\Elomock\PDOMock;

class PrepareTest extends TestCase
{
    #[Test]
    public function itShouldHandlePreparedStatement(): void
    {
        $scenario = function (PDO $pdo) {
            $statement = $pdo->prepare('select * from "books"');

            $result = $statement->execute();

            static::assertTrue($result);
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();
        $mock->expect('select * from "books"')->toBePrepared();
        $scenario($mock);
    }

    #[Test]
    public function itShouldFailOnUnexpectedQuery(): void
    {
        $pdo = new PDOMock();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query: select * from "users"');

        $pdo->prepare('select * from "users"');
    }

    #[Test]
    public function itShouldFailWhenStatementIsNotPrepared(): void
    {
        $pdo = new PDOMock();
        $pdo->expect('select * from "users"')->toBePrepared();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Statement is not prepared');

        $pdo->exec('select * from "users"');
    }

    #[Test]
    public function itShouldFailWhenStatementIsNotExecuted(): void
    {
        $this->markTestSkipped('To be implemented');

        $pdo = new PDOMock();

        $pdo->expect('select * from "users"')
            ->toBePrepared()
            ->toBeExecuted(false);

        $pdo->prepare('select * from "users"');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Some expectations were not fulfilled');

        $pdo->assertExpectationsFulfilled();
    }
}
