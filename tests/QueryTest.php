<?php

namespace Tests\Xalaida\PDOMock;

use PDOStatement;
use Xalaida\PDOMock\PDOMock;

class QueryTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function itShouldFetchRowsUsingQuery()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->willFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        $statement = $pdo->query('select * from "books"');

        $this->assertInstanceOf(PDOStatement::class, $statement);

        $rows = $statement->fetchAll($pdo::FETCH_OBJ);

        static::assertCount(2, $rows);
        static::assertIsObjectType($rows[0]);
        static::assertEquals((object) ['id' => 1, 'title' => 'Kaidash’s Family'], $rows[0]);
        static::assertIsObjectType($rows[1]);
        static::assertEquals((object) ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'], $rows[1]);
    }

    /**
     * @test
     * @return void
     */
    public function itShouldFailWhenExpectParamsUsingQueryMethod()
    {
        $pdo = new PDOMock();

        $pdo->expect('delete from "posts" where "status" = ?')
            ->with(['draft']);

        $this->expectException(static::getExpectationFailedExceptionClass());
        $this->expectExceptionMessage('Params do not match.');

        $pdo->query('delete from "posts" where "status" = ?');
    }

    /**
     * @test
     * @return void
     */
    public function itShouldHandleQueryAsPreparedStatement()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared();

        $statement = $pdo->query('select * from "books"');

        $this->assertInstanceOf(PDOStatement::class, $statement);
        $this->assertSame(0, $statement->rowCount());

        $pdo->assertExpectationsFulfilled();
    }
}
