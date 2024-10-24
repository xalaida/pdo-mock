<?php

namespace Tests\Xala\Elomock;

use PDOException;
use PDOStatement;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Xala\Elomock\FakePDO;

class QueryTest extends TestCase
{
    #[Test]
    public function itShouldFetchRowsUsingQuery(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "users"')
            ->toBePrepared()
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
        $pdo = new FakePDO();

        $pdo->expectQuery('delete from "posts" where "status" = ?')
            ->toBePrepared()
            ->withBindings(['draft']);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Bindings do not match');

        $pdo->query('delete from "posts" where "status" = ?');
    }

    #[Test]
    public function itShouldThrowPDOException()
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from table "posts"')
            ->toBePrepared()
            ->andFail('SQL syntax error');

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('SQL syntax error');

        $pdo->query('select * from table "posts"');
    }

    #[Test]
    public function itShouldFailWhenQueryIsNotPrepared()
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "posts"');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Statement is not prepared');

        $pdo->query('select * from "posts"');
    }

    #[Test]
    public function itShouldExecQueryMultipleTimes()
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('delete from "users" limit 1')
            ->toBePrepared();

        $pdo->expectQuery('delete from "users" limit 1')
            ->toBePrepared();

        $statement = $pdo->query('delete from "users" limit 1');

        $result = $statement->execute();

        static::assertTrue($result);

        $pdo->assertExpectationsFulfilled();
    }
}
