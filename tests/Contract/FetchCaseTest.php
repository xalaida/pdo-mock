<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class FetchCaseTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldHandleFetchCaseNatural($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_CASE, $pdo::CASE_NATURAL);

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $row = $statement->fetch($pdo::FETCH_BOTH);

        static::assertEquals([0 => 1, 'Id' => 1, 1 => 'Kaidash’s Family', 'Title' => 'Kaidash’s Family'], $row);

        $row = $statement->fetch($pdo::FETCH_OBJ);

        static::assertEquals((object) ['Id' => 2, 'Title' => 'Shadows of the Forgotten Ancestors'], $row);

        static::assertFalse(
            $statement->fetch()
        );
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldHandleFetchCaseUpper($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_CASE, $pdo::CASE_UPPER);

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $row = $statement->fetch($pdo::FETCH_BOTH);

        static::assertEquals([0 => 1, 'ID' => 1, 1 => 'Kaidash’s Family', 'TITLE' => 'Kaidash’s Family'], $row);

        $row = $statement->fetch($pdo::FETCH_OBJ);

        static::assertEquals((object) ['ID' => 2, 'TITLE' => 'Shadows of the Forgotten Ancestors'], $row);

        static::assertFalse(
            $statement->fetch()
        );
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldHandleFetchCaseLower($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_CASE, $pdo::CASE_LOWER);

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $row = $statement->fetch($pdo::FETCH_BOTH);

        static::assertEquals([0 => 1, 'id' => 1, 1 => 'Kaidash’s Family', 'title' => 'Kaidash’s Family'], $row);

        $row = $statement->fetch($pdo::FETCH_OBJ);

        static::assertEquals((object) ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'], $row);

        static::assertFalse(
            $statement->fetch()
        );
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

        $pdo->exec('create table "books" ("Id" integer primary key autoincrement not null, "Title" varchar not null)');

        $pdo->exec('insert into "books" ("Title") values ("Kaidash’s Family"), ("Shadows of the Forgotten Ancestors")');

        return $pdo;
    }

    /**
     * @return PDOMock
     */
    protected static function configureMock()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->willFetchRows([
                ['Id' => 1, 'Title' => 'Kaidash’s Family'],
                ['Id' => 2, 'Title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        return $pdo;
    }
}
