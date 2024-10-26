<?php

namespace Tests\Xala\Elomock;

use PDOException;
use PDOStatement;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Xala\Elomock\PDOMock;

class QueryTest extends TestCase
{
    #[Test]
    public function itShouldFetchRowsUsingQuery(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "users"')
            ->andFetchRows([
                ['id' => 1, 'name' => 'john'],
                ['id' => 2, 'name' => 'jane'],
            ]);

        $statement = $pdo->query('select * from "users"');

        $this->assertInstanceOf(PDOStatement::class, $statement);

        $rows = $statement->fetchAll($pdo::FETCH_OBJ);

        static::assertCount(2, $rows);
        static::assertIsObject($rows[0]);
        static::assertEquals((object) ['id' => 1, 'name' => 'john'], $rows[0]);
        static::assertIsObject($rows[1]);
        static::assertEquals((object) ['id' => 2, 'name' => 'jane'], $rows[1]);
    }

    #[Test]
    public function itShouldFailWhenExpectBindingsUsingQuery(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('delete from "posts" where "status" = ?')
            ->withBindings(['draft']);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Bindings do not match');

        $pdo->query('delete from "posts" where "status" = ?');
    }

    #[Test]
    public function itShouldHandleQueryAsPreparedStatement()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "posts"')
            ->toBePrepared();

        $statement = $pdo->query('select * from "posts"');

        $this->assertInstanceOf(PDOStatement::class, $statement);
        $this->assertSame(0, $statement->rowCount());

        $pdo->assertExpectationsFulfilled();
    }

    #[Test]
    public function itShouldExecQueryMultipleTimes()
    {
        $pdo = new PDOMock();

        $pdo->expect('delete from "users" limit 1');

        $pdo->expect('delete from "users" limit 1');

        $statement = $pdo->query('delete from "users" limit 1');

        $result = $statement->execute();

        static::assertTrue($result);

        $pdo->assertExpectationsFulfilled();
    }
}
