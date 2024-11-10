<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class FetchModeObjTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldHandleFetchInObjMode($pdo)
    {
        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $row = $statement->fetch($pdo::FETCH_OBJ);

        static::assertIsObjectType($row);
        static::assertEquals((object) ['id' => 1, 'title' => 'Kaidash’s Family'], $row);

        $row = $statement->fetch($pdo::FETCH_OBJ);

        static::assertIsObjectType($row);
        static::assertEquals((object) ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'], $row);

        $row = $statement->fetch($pdo::FETCH_OBJ);

        static::assertFalse($row);
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldHandleFetchAllInObjMode($pdo)
    {
        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $rows = $statement->fetchAll($pdo::FETCH_OBJ);

        static::assertCount(2, $rows);
        static::assertIsObjectType($rows[0]);
        static::assertEquals((object) ['id' => 1, 'title' => 'Kaidash’s Family'], $rows[0]);
        static::assertIsObjectType($rows[1]);
        static::assertEquals((object) ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'], $rows[1]);
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

        $pdo->exec('create table "books" ("id" integer primary key autoincrement not null, "title" varchar not null)');

        $pdo->exec('insert into "books" ("title") values ("Kaidash’s Family"), ("Shadows of the Forgotten Ancestors")');

        return $pdo;
    }

    /**
     * @return PDOMock
     */
    protected static function configureMock()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->andFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        return $pdo;
    }
}
