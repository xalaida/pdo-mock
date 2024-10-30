<?php

namespace Tests\Xala\Elomock\Contract;

use PDO;
use Tests\Xala\Elomock\TestCase;
use Xala\Elomock\PDOMock;

class PreparedRowCountNotExecutedTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     */
    public function itShouldReturnRowCountUsingNotExecutedPreparedStatement(PDO $pdo)
    {
        $statement = $pdo->prepare('delete from "books"');

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

        return $pdo;
    }

    protected static function configureMock()
    {
        $pdo = new PDOMock();

        $pdo->expect('delete from "books"')
            ->affecting(2);

        return $pdo;
    }
}
