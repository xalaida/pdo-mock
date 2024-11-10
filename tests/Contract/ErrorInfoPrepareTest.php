<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class ErrorInfoPrepareTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldDisplayErrorInformationForSuccessfullyPreparedStatement($pdo)
    {
        $statement = $pdo->prepare('insert into "books" ("id", "title") values (1, "Stolen Happiness by Ivan Franko")');

        static::assertNull($statement->errorCode());
        static::assertSame(['', null, null], $statement->errorInfo());

        static::assertSame('00000', $pdo->errorCode());
        static::assertSame(['00000', null, null], $pdo->errorInfo());
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldDisplayErrorInformationForSuccessfullyExecutedPreparedStatement($pdo)
    {
        $statement = $pdo->prepare('insert into "books" ("id", "title") values (1, "Stolen Happiness by Ivan Franko")');

        $statement->execute();

        static::assertSame('00000', $statement->errorCode());
        static::assertSame(['00000', null, null], $statement->errorInfo());

        static::assertSame('00000', $pdo->errorCode());
        static::assertSame(['00000', null, null], $pdo->errorInfo());
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

        return $pdo;
    }

    /**
     * @return PDOMock
     */
    protected static function configureMock()
    {
        $pdo = new PDOMock();

        $pdo->expect('insert into "books" ("id", "title") values (1, "Stolen Happiness by Ivan Franko")');

        return $pdo;
    }
}
