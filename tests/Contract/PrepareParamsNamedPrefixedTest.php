<?php

namespace Tests\Xala\Elomock\Contract;

use PDO;
use Tests\Xala\Elomock\TestCase;
use Xala\Elomock\PDOMock;

class PrepareParamsNamedPrefixedTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     */
    public function itShouldHandleBindValue(PDO $pdo): void
    {
        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        static::assertTrue(
            $statement->bindValue(':year', 2024, $pdo::PARAM_INT),
        );

        static::assertTrue(
            $statement->bindValue(':status', 'published', $pdo::PARAM_STR),
        );

        static::assertTrue(
            $statement->execute(),
        );

        static::assertCount(2, $statement->fetchAll());
    }

    /**
     * @test
     * @dataProvider contracts
     */
    public function itShouldHandleBindParam(PDO $pdo): void
    {
        $status = 'published';
        $year = 2024;

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        static::assertTrue(
            $statement->bindParam(':year', $year, $pdo::PARAM_INT),
        );

        static::assertTrue(
            $statement->bindParam(':status', $status, $pdo::PARAM_STR),
        );

        static::assertTrue(
            $statement->execute(),
        );

        static::assertCount(2, $statement->fetchAll());
    }

    public static function contracts(): array
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

    protected static function configureSqlite(): PDO
    {
        $pdo = new PDO('sqlite::memory:');

        $pdo->exec('create table "books" ("id" integer primary key autoincrement not null, "title" varchar not null, "status" varchar not null, "year" integer not null)');

        $pdo->exec('insert into "books" ("title", "status", "year") values ("Kaidash’s Family", "published", 2024), ("Shadows of the Forgotten Ancestors", "published", 2024)');

        return $pdo;
    }

    protected static function configureMock(): PDOMock
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = :status and "year" = :year')
            ->toBePrepared()
            ->withParam(':status', 'published', $pdo::PARAM_STR)
            ->withParam(':year', 2024, $pdo::PARAM_INT)
            ->andFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        return $pdo;
    }
}
