<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Xala\Elomock\FakePDO;

class PreparedStatementTest extends TestCase
{
    #[Test]
    public function itShouldHandlePreparedStatement(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "users"')
            ->toBePrepared();

        $statement = $pdo->prepare('select * from "users"');

        $result = $statement->execute();

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldFailWhenStatementIsNotExecuted(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "users"')
            ->toBePrepared();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Some expectations were not fulfilled');

        $pdo->prepare('select * from "users"');

        $pdo->assertExpectationsFulfilled();
    }

    #[Test]
    public function itShouldFailWhenStatementIsNotPrepared(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "users"')
            ->toBePrepared();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Statement is not prepared');

        $pdo->exec('select * from "users"');
    }
}
