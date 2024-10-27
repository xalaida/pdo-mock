<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Xala\Elomock\PDOMock;

class PrepareTest extends TestCase
{
    #[Test]
    public function itShouldHandlePreparedStatement(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared();

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldFailOnUnexpectedQuery(): void
    {
        $pdo = new PDOMock();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query: select * from "books"');

        $pdo->prepare('select * from "books"');
    }

    #[Test]
    public function itShouldFailWhenStatementIsNotPrepared(): void
    {
        $pdo = new PDOMock();
        $pdo->expect('select * from "books"')->toBePrepared();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Statement is not prepared');

        $pdo->exec('select * from "books"');
    }

    #[Test]
    public function itShouldFailWhenStatementIsNotExecuted(): void
    {
        $this->markTestSkipped('To be implemented');

        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared()
            ->toBeExecuted(false);

        $pdo->prepare('select * from "books"');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Some expectations were not fulfilled');

        $pdo->assertExpectationsFulfilled();
    }
}
