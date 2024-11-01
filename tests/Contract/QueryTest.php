<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use PDOStatement;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class QueryTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldFetchAsObjects($pdo)
    {
        $statement = $pdo->query('select * from "books"', $pdo::FETCH_OBJ);

        $rows = $statement->fetchAll();

        static::assertCount(2, $rows);
        static::assertIsObjectType($rows[0]);
        static::assertSame(1, $rows[0]->id);
        static::assertSame('Kaidash’s Family', $rows[0]->title);
        static::assertIsObjectType($rows[1]);
        static::assertSame(2, $rows[1]->id);
        static::assertSame('Shadows of the Forgotten Ancestors', $rows[1]->title);
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
        $pdo = new PDOMock('sqlite');

        $pdo->expect('select * from "books"')
            ->andFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        return $pdo;
    }
}
