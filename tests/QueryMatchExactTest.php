<?php

namespace Tests\Xalaida\PDOMock;

use Xalaida\PDOMock\PDOMock;

class QueryMatchExactTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function itShouldMatchExactQuery()
    {
        $pdo = new PDOMock();

        $expectation = $pdo->expect('select * from "books" where id = 7');
        $expectation->toBeExact();

        $result = $pdo->exec('select * from "books" where id = 7');

        static::assertSame(0, $result);

        $pdo->assertExpectationsFulfilled();
    }

    /**
     * @test
     * @return void
     */
    public function itShouldFailWhenExactQueryDoesNotMatch()
    {
        $pdo = new PDOMock();

        $expectation = $pdo->expect('insert select * from "books" where id = 7 ');
        $expectation->toBeExact();

        $this->expectException(static::getExpectationFailedExceptionClass());
        $this->expectExceptionMessage('Query does not match.');

        $pdo->exec('select * from "books" where id = 7');
    }
}
