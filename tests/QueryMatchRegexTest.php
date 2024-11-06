<?php

namespace Tests\Xalaida\PDOMock;

use Xalaida\PDOMock\PDOMock;

class QueryMatchRegexTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldMatchMultilineQuery()
    {
        $pdo = new PDOMock();

        $expectation = $pdo->expect('
            select * 
            from "books"
            where id = 7
        ');

        $expectation->toMatchRegex();

        $result = $pdo->exec('select * from "books" where id = 7');

        static::assertSame(0, $result);

        $pdo->assertExpectationsFulfilled();
    }
}
