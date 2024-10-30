<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use PDOStatement;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class QueryFetchAllTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     */
    public function itShouldFetchRowsUsingQuery($pdo)
    {
        $statement = $pdo->query('select * from "books"');

        $rows = $statement->fetchAll($pdo::FETCH_OBJ);

        static::assertCount(2, $rows);
        static::assertIsObject($rows[0]);
        static::assertEquals((object) ['id' => 1, 'title' => 'Kaidash’s Family'], $rows[0]);
        static::assertIsObject($rows[1]);
        static::assertEquals((object) ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'], $rows[1]);

        $this->assertInstanceOf(PDOStatement::class, $statement);
        static::assertSame(0, $statement->rowCount());
    }

    public static function contracts()
    {
        return [
            'SQLite' => [
                static::configureSqlite(),
            ],

            'Mock' => [
                static::configureMock(),
            ],
        ];
    }

    protected static function configureSqlite()
    {
        $pdo = new PDO('sqlite::memory:');

        $pdo->exec('create table "books" ("id" integer primary key autoincrement not null, "title" varchar not null)');

        $pdo->exec('insert into "books" ("title") values ("Kaidash’s Family"), ("Shadows of the Forgotten Ancestors")');

        return $pdo;
    }

    protected static function configureMock()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->andFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        return $pdo;
    }
}
