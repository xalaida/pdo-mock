<?php

namespace Tests\Xala\Elomock;

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

        $pdo->expect('select * from "books"')
            ->andFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        $statement = $pdo->query('select * from "books"');

        $this->assertInstanceOf(PDOStatement::class, $statement);

        $rows = $statement->fetchAll($pdo::FETCH_OBJ);

        static::assertCount(2, $rows);
        static::assertIsObject($rows[0]);
        static::assertEquals((object) ['id' => 1, 'title' => 'Kaidash’s Family'], $rows[0]);
        static::assertIsObject($rows[1]);
        static::assertEquals((object) ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'], $rows[1]);
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
    public function itShouldHandleQueryAsPreparedStatement(): void
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
