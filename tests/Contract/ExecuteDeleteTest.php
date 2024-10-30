<?php

namespace Tests\Xala\Elomock\Contract;

use PDO;
use Tests\Xala\Elomock\TestCase;
use Xala\Elomock\PDOMock;

class ExecuteDeleteTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     */
    public function itShouldReturnAffectedRowsOnExecute(PDO $pdo)
    {
        $result = $pdo->exec('delete from "books"');

        static::assertSame(0, $result);
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

        $pdo->expect('delete from "books"');

        return $pdo;
    }
}
