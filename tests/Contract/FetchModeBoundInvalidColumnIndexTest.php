<?php

namespace Tests\Xala\Elomock\Contract;

use PDO;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\Xala\Elomock\TestCase;
use Xala\Elomock\PDOMock;

class FetchModeBoundInvalidColumnIndexTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     */
    public function itShouldThrowValueExceptionWhenInvalidColumnIndex(PDO $pdo)
    {
        // TODO: it throws even with silent mode in php >= 8 but not in < 8
        $pdo->setAttribute($pdo::ATTR_ERRMODE, $pdo::ERRMODE_EXCEPTION);

        $statement = $pdo->prepare('select "title" from "books"');

        $statement->setFetchMode($pdo::FETCH_BOUND);

        $statement->bindColumn(1, $title);
        $statement->bindColumn(2, $status);

        $result = $statement->execute();

        static::assertTrue($result);

        try {
            $statement->fetch();

            if (PHP_VERSION_ID >= 71000) {
                $this->fail('Expected exception was not thrown');
            }

            static::assertSame("Kaidash’s Family", $title);
            static::assertSame('', $status);
        } catch (\Throwable $e) {
            if ($e instanceof ExpectationFailedException) {
                throw $e;
            }

            if (PHP_VERSION_ID >= 80000) {
                static::assertInstanceOf(\ValueError::class, $e);
                static::assertSame('Kaidash’s Family', $title);
                static::assertSame('Invalid column index', $e->getMessage());
            } else {
                static::assertInstanceOf(\PDOException::class, $e);
                static::assertSame('Kaidash’s Family', $title);
                static::assertSame('SQLSTATE[HY000]: General error: Invalid column index', $e->getMessage());
            }
        }
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

        $pdo->exec('create table "books" (
            "id" integer primary key autoincrement not null, 
            "title" varchar not null
        )');

        $pdo->exec('insert into "books" ("title") values ("Kaidash’s Family")');

        return $pdo;
    }

    protected static function configureMock(): PDOMock
    {
        $pdo = new PDOMock();

        $pdo->expect('select "title" from "books"')
            ->andFetchRows([
                ['title' => 'Kaidash’s Family'],
            ]);

        return $pdo;
    }
}
