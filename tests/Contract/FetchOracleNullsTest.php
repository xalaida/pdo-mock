<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class FetchOracleNullsTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldHandleFetchOracleNullNatural($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_ORACLE_NULLS, $pdo::NULL_NATURAL);

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $row = $statement->fetch($pdo::FETCH_ASSOC);

        static::assertSame('', $row['title']);

        $row = $statement->fetch($pdo::FETCH_ASSOC);

        static::assertNull($row['title']);

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
    public function itShouldHandleFetchOracleNullEmptyString($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_ORACLE_NULLS, $pdo::NULL_EMPTY_STRING);

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $row = $statement->fetch($pdo::FETCH_ASSOC);

        static::assertNull($row['title']);

        $row = $statement->fetch($pdo::FETCH_ASSOC);

        static::assertNull($row['title']);

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
    public function itShouldHandleFetchOracleNullToString($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_STRINGIFY_FETCHES, false);
        $pdo->setAttribute($pdo::ATTR_ORACLE_NULLS, $pdo::NULL_TO_STRING);

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $row = $statement->fetch($pdo::FETCH_ASSOC);

        static::assertSame('', $row['title']);

        $row = $statement->fetch($pdo::FETCH_ASSOC);

        static::assertSame('', $row['title']);

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
    public function itShouldHandleFetchOracleNullEmptyStringInFetchStringifyMode($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_STRINGIFY_FETCHES, true);
        $pdo->setAttribute($pdo::ATTR_ORACLE_NULLS, $pdo::NULL_EMPTY_STRING);

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $row = $statement->fetch($pdo::FETCH_ASSOC);

        static::assertNull($row['title']);

        $row = $statement->fetch($pdo::FETCH_ASSOC);

        static::assertNull($row['title']);

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

        $pdo->exec('create table "books" ("id" integer primary key autoincrement not null, "title" varchar null)');

        $pdo->exec('insert into "books" ("title") values (""), (null)');

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
                ['id' => 1, 'title' => ''],
                ['id' => 2, 'title' => null],
            ]);

        return $pdo;
    }
}
