<?php

namespace Tests\Xala\Elomock\Contract;

use PDO;
use Tests\Xala\Elomock\TestCase;
use Xala\Elomock\PDOMock;

class PreparedRowCountExecutedTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     */
    public function itShouldReturnAffectedRowsUsingPreparedStatement(PDO $pdo)
    {
        $statement = $pdo->prepare('insert into "books" ("title") values ("Shadows of the Forgotten Ancestors"), ("Kaidash’s Family")');

        $statement->execute();

        static::assertSame(2, $statement->rowCount());
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
