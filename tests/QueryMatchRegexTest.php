<?php

namespace Tests\Xalaida\PDOMock;

use Xalaida\PDOMock\PDOMock;

class QueryMatchRegexTest extends TestCase
{
    /**
     * @test
     * @return void
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

    /**
     * @test
     * @return void
     */
    public function itShouldMatchRegex()
    {
        $pdo = new PDOMock();

        $expectation = $pdo->expect('insert into "books" ({{ .* }}) values ({{ .* }})');

        $expectation->toMatchRegex();

        $statement = $pdo->prepare('insert into "books" ("title", "status", "year", "author") values (?, ?, ?, ?)');

        $result = $statement->execute(['The Forest Song', 'published', 2020, 'Lesya']);

        static::assertTrue($result);

        $pdo->assertExpectationsFulfilled();
    }

    /**
     * @test
     * @return void
     */
    public function itShouldFailWhenRegexDoesNotMatch()
    {
        $pdo = new PDOMock();

        $expectation = $pdo->expect('insert into "books" ({{ [0-9]+ }}) values (?, ?, ?, ?)');

        $expectation->toMatchRegex();

        $statement = $pdo->prepare('insert into "books" ("title", "status", "year", "author") values (?, ?, ?, ?)');

        $this->expectException(static::getExpectationFailedExceptionClass());
        $this->expectExceptionMessage('Query does not match.');

        $statement->execute(['The Forest Song', 'published', 2020, 'Lesya']);
    }
}
