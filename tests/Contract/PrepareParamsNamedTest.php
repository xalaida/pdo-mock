<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class PrepareParamsNamedTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldHandleBindValue($pdo)
    {
        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        static::assertTrue(
            $statement->bindValue('year', 2024, $pdo::PARAM_INT)
        );

        static::assertTrue(
            $statement->bindValue('status', 'published', $pdo::PARAM_STR)
        );

        static::assertTrue(
            $statement->execute()
        );

        static::assertCount(2, $statement->fetchAll());
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldHandleBindParam($pdo)
    {
        $status = 'published';
        $year = 2024;

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        static::assertTrue(
            $statement->bindParam('year', $year, $pdo::PARAM_INT)
        );

        static::assertTrue(
            $statement->bindParam('status', $status, $pdo::PARAM_STR)
        );

        static::assertTrue(
            $statement->execute()
        );

        static::assertCount(2, $statement->fetchAll());
    }

    /**
     * @return array<string, array<int, PDO>>
     */
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

    /**
     * @return PDO
     */
    protected static function configureSqlite()
    {
        $pdo = new PDO('sqlite::memory:');

        $pdo->exec('create table "books" ("id" integer primary key autoincrement not null, "title" varchar not null, "status" varchar not null, "year" integer not null)');

        $pdo->exec('insert into "books" ("title", "status", "year") values ("Kaidash’s Family", "published", 2024), ("Shadows of the Forgotten Ancestors", "published", 2024)');

        return $pdo;
    }

    /**
     * @return PDOMock
     */
    protected static function configureMock()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = :status and "year" = :year')
            ->toBePrepared()
            ->withParam('status', 'published', $pdo::PARAM_STR)
            ->withParam('year', 2024, $pdo::PARAM_INT)
            ->willFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        return $pdo;
    }
}
