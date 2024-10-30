<?php

namespace Tests\Xala\Elomock\Contract;

use PDO;
use Tests\Xala\Elomock\TestCase;
use Xala\Elomock\PDOMock;

class PrepareTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     */
    public function itShouldHandlePreparedStatement(PDO $pdo)
    {
        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);
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

        $pdo->expect('select * from "books"')
            ->toBePrepared();

        return $pdo;
    }
}
