<?php

namespace Tests\Xala\Elomock\Contract;

use PDO;
use Tests\Xala\Elomock\TestCase;
use Xala\Elomock\PDOMock;

class PrepareParamsTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     */
    public function itShouldHandleBindValue(PDO $pdo)
    {
        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ?');

        static::assertTrue(
            $statement->bindValue(1, 'published', $pdo::PARAM_STR)
        );

        static::assertTrue(
            $statement->bindValue(2, 2024, $pdo::PARAM_INT)
        );

        static::assertTrue(
            $statement->execute()
        );

        static::assertCount(2, $statement->fetchAll());
    }

    /**
     * @test
     * @dataProvider contracts
     */
    public function itShouldHandleBindParam(PDO $pdo)
    {
        $status = 'published';
        $year = 2024;

        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ?');

        static::assertTrue(
            $statement->bindParam(1, $status, $pdo::PARAM_STR, 10)
        );

        static::assertTrue(
            $statement->bindParam(2, $year, $pdo::PARAM_INT)
        );

        static::assertTrue(
            $statement->execute()
        );

        static::assertCount(2, $statement->fetchAll());
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

        $pdo->exec('create table "books" ("id" integer primary key autoincrement not null, "title" varchar not null, "status" varchar not null, "year" integer not null)');

        $pdo->exec('insert into "books" ("title", "status", "year") values ("Kaidash’s Family", "published", 2024), ("Shadows of the Forgotten Ancestors", "published", 2024)');

        return $pdo;
    }

    protected static function configureMock()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = ? and "year" = ?')
            ->toBePrepared()
            ->withParam(1, 'published', $pdo::PARAM_STR)
            ->withParam(2, 2024, $pdo::PARAM_INT)
            ->andFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        return $pdo;
    }
}
