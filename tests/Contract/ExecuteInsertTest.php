<?php

namespace Tests\Xala\Elomock\Contract;

use PDO;
use Tests\Xala\Elomock\TestCase;
use Xala\Elomock\PDOMock;

class ExecuteInsertTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     */
    public function itShouldReturnAffectedRowsOnExecute(PDO $pdo)
    {
        $result = $pdo->exec('insert into "books" ("title") values ("Shadows of the Forgotten Ancestors"), ("Kaidash’s Family")');

        static::assertSame(2, $result);
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

        return $pdo;
    }

    protected static function configureMock()
    {
        $pdo = new PDOMock();

        $pdo->expect('insert into "books" ("title") values ("Shadows of the Forgotten Ancestors"), ("Kaidash’s Family")')
            ->affecting(2);

        return $pdo;
    }
}
