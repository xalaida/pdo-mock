<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class FetchModeLazyTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldFailOnFetchAllInLazyMode($pdo)
    {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $statement = $pdo->prepare('select * from "books"');

        if (PHP_VERSION_ID < 80000) {
            try {
                $statement->fetchAll($pdo::FETCH_LAZY);

                $this->fail('Expected exception is not thrown');
            } catch (\PDOException $e) {
                static::assertSame("SQLSTATE[HY000]: General error: PDO::FETCH_LAZY can't be used with PDOStatement::fetchAll()", $e->getMessage());
            }
        } else {
            try {
                $statement->fetchAll($pdo::FETCH_LAZY);

                $this->fail('Expected exception is not thrown');
            } catch (\ValueError $e) {
                static::assertSame('PDOStatement::fetchAll(): Argument #1 ($mode) cannot be PDO::FETCH_LAZY in PDOStatement::fetchAll()', $e->getMessage());
            }
        }
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

        $pdo->exec('create table "books" ("id" integer primary key autoincrement not null, "title" varchar not null, "price" integer)');

        $pdo->exec('insert into "books" ("title", "price") values ("Kaidash’s Family", 1500), ("Shadows of the Forgotten Ancestors", null)');

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
                ['id' => 1, 'title' => 'Kaidash’s Family', 'price' => 1500],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors', 'price' => null],
            ]);

        return $pdo;
    }
}
