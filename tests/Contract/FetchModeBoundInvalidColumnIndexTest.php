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
     */
    public function itShouldThrowValueExceptionWhenInvalidColumnIndex($pdo)
    {
        // TODO: it throws even with silent mode in php >= 8 but not in < 8
        $pdo->setAttribute($pdo::ATTR_ERRMODE, $pdo::ERRMODE_EXCEPTION);

        $statement = $pdo->prepare('select "title" from "books"');

        $statement->setFetchMode($pdo::FETCH_BOUND);

        $statement->bindColumn(1, $title);
        $statement->bindColumn(2, $status);

        $result = $statement->execute();

        static::assertTrue($result);

        if (PHP_VERSION_ID < 70100) {
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
                static::assertSame(null, $status);

                static::assertInstanceOf(\ValueError::class, $e);
                static::assertSame('Kaidash’s Family', $title);
                static::assertSame('Invalid column index', $e->getMessage());
            }
        }
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

        $pdo->exec('create table "books" (
            "id" integer primary key autoincrement not null, 
            "title" varchar not null
        )');

        $pdo->exec('insert into "books" ("title") values ("Kaidash’s Family")');

        return $pdo;
    }

    protected static function configureMock()
    {
        $pdo = new PDOMock('sqlite');

        $pdo->expect('select "title" from "books"')
            ->andFetchRows([
                ['title' => 'Kaidash’s Family'],
            ]);

        return $pdo;
    }
}
