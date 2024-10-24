<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Xala\Elomock\FakePDO;

class PreparedStatementTest extends TestCase
{
    // TODO: it should fail if statement was not executed

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
    public function itShouldFailWhenStatementIsNotPrepared(): void
    {


//        $pdo = new FakePDO();
//
//        $pdo->expectQuery('select * from "users"')
//            ->toBePrepared();

//        $this->expectException(ExpectationFailedException::class);
//        $this->expectExceptionMessage('Statement is not prepared');

        $statement = $pdo->exec('select * from "users"');
    }
}
