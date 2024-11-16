<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class FetchModeBoundInvalidColumnIndexTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldThrowValueExceptionWhenInvalidColumnIndex($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_ERRMODE, $pdo::ERRMODE_EXCEPTION);
        $pdo->setAttribute($pdo::ATTR_STRINGIFY_FETCHES, true);
        $pdo->setAttribute($pdo::ATTR_ORACLE_NULLS, $pdo::NULL_TO_STRING);

        $statement = $pdo->prepare('select "title" from "books"');

        $statement->setFetchMode($pdo::FETCH_BOUND);

        $statement->bindColumn(1, $title);
        $statement->bindColumn(2, $status);

        $result = $statement->execute();

        static::assertTrue($result);

        if (PHP_VERSION_ID < 80000) {
            try {
                $statement->fetch();

                static::assertSame("Kaidash’s Family", $title);
                static::assertSame('', $status);
            } catch (\Exception $e) {
                static::assertInstanceOf(\PDOException::class, $e);
                static::assertSame('Kaidash’s Family', $title);
                static::assertSame('SQLSTATE[HY000]: General error: Invalid column index', $e->getMessage());
            }
        } else {
            try {
                $statement->fetch();

                $this->fail('Expected exception was not thrown');
            } catch (\Throwable $e) {
                static::assertSame("Kaidash’s Family", $title);
                static::assertNull($status);

                static::assertInstanceOf(\ValueError::class, $e);
                static::assertSame('Kaidash’s Family', $title);
                static::assertSame('Invalid column index', $e->getMessage());
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

        $pdo->exec('create table "books" (
            "id" integer primary key autoincrement not null, 
            "title" varchar not null
        )');

        $pdo->exec('insert into "books" ("title") values ("Kaidash’s Family")');

        return $pdo;
    }

    /**
     * @return PDOMock
     */
    protected static function configureMock()
    {
        $pdo = new PDOMock();

        $pdo->expect('select "title" from "books"')
            ->willFetchRows([
                ['title' => 'Kaidash’s Family'],
            ]);

        return $pdo;
    }
}
