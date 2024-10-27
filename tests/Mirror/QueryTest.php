<?php

namespace Tests\Xala\Elomock\Mirror;

use PDO;
use PDOStatement;
use PHPUnit\Framework\Attributes\Test;
use Xala\Elomock\PDOMock;

class QueryTest extends TestCase
{
    #[Test]
    public function itShouldFetchRowsUsingQuery(): void
    {
        $scenario = function (PDO $pdo) {
            $statement = $pdo->query('select * from "books"');

            $this->assertInstanceOf(PDOStatement::class, $statement);

            $rows = $statement->fetchAll($pdo::FETCH_OBJ);

            static::assertCount(2, $rows);
            static::assertIsObject($rows[0]);
            static::assertEquals((object) ['id' => 1, 'title' => 'Kaidash’s Family'], $rows[0]);
            static::assertIsObject($rows[1]);
            static::assertEquals((object) ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'], $rows[1]);
        };

        $sqlite = $this->sqlite();
        $sqlite->exec('insert into "books" ("title") values ("Kaidash’s Family"), ("Shadows of the Forgotten Ancestors")');
        $scenario($sqlite);

        $mock = new PDOMock();
        $mock->expect('select * from "books"')
            ->andFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);
        $scenario($mock);
    }

    #[Test]
    public function itShouldHandleQueryAsPreparedStatement(): void
    {
        $scenario = function (PDO $pdo) {
            $statement = $pdo->query('select * from "books"');

            $this->assertInstanceOf(PDOStatement::class, $statement);
            $this->assertSame(0, $statement->rowCount());
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();
        $mock->expect('select * from "books"')->toBePrepared();

        $scenario($mock);

        $mock->assertExpectationsFulfilled();
    }
}
