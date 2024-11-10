<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use PDOException;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class TransactionNestedTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldHandleNestedTransactions($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_ERRMODE, $pdo::ERRMODE_SILENT);

        $pdo->beginTransaction();

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('There is already an active transaction');

        $pdo->beginTransaction();

        static::assertTrue($pdo->inTransaction());
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

        $pdo->expectBeginTransaction();
        $pdo->expectBeginTransaction();

        return $pdo;
    }
}
