<?php

namespace Tests\Xalaida\PDOMock;

use Xalaida\PDOMock\PDOMock;

class ExecuteTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldExecuteQuery()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"');

        $result = $pdo->exec('select * from "books"');

        static::assertSame(0, $result);
    }

    /**
     * @test
     */
    public function itShouldCalculatesRowCountUsingFetchRows()
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

    /**
     * @test
     */
    public function itShouldFailOnUnexpectedQuery()
    {
        $pdo = new PDOMock();

        $this->expectExceptionMessage('Unexpected query: select * from "books"');

        $pdo->exec('select * from "books"');
    }

    /**
     * @test
     */
    public function itShouldFailWhenQueryDoesntMatch()
    {
        $pdo = new PDOMock();
        $pdo->expect('select * from "categories"');

        $this->expectExceptionMessage('Unexpected query: select * from "books"');

        $pdo->exec('select * from "books"');
    }

    /**
     * @test
     */
    public function itShouldFailWhenQueryIsNotExecuted()
    {
        $pdo = new PDOMock();
        $pdo->expect('select * from "books"');

        $this->expectExceptionMessage('Some expectations were not fulfilled.');

        $pdo->assertExpectationsFulfilled();
    }
}
