<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class PrepareParamsTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldHandleBindValue($pdo)
    {
        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ?');

        static::assertTrue(
            $statement->bindValue(1, 'published', $pdo::PARAM_STR)
        );

        static::assertTrue(
            $statement->bindValue(2, 2024, $pdo::PARAM_INT)
        );

        static::assertTrue(
            $statement->execute()
        );

        static::assertCount(2, $statement->fetchAll());
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldHandleBindParam($pdo)
    {
        $status = 'published';
        $year = 2024;

        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ?');

        static::assertTrue(
            $statement->bindParam(1, $status, $pdo::PARAM_STR, 10)
        );

        static::assertTrue(
            $statement->bindParam(2, $year, $pdo::PARAM_INT)
        );

        static::assertTrue(
            $statement->execute()
        );

        static::assertCount(2, $statement->fetchAll());
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldFailOnBindValueWithZeroBasedIndex($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_ERRMODE, $pdo::ERRMODE_EXCEPTION);

        $statement = $pdo->prepare('select * from "books" where "id" = ?');

        if (PHP_VERSION_ID < 80000) {
            try {
                $statement->bindValue(0, 7);

                $this->fail('Expected exception is not thrown');
            } catch (\PDOException $e) {
                static::assertSame('SQLSTATE[HY093]: Invalid parameter number: Columns/Parameters are 1-based', $e->getMessage());
            }
        } else {
            try {
                $statement->bindValue(0, 7);

                $this->fail('Expected exception is not thrown');
            } catch (\ValueError $e) {
                static::assertSame('PDOStatement::bindValue(): Argument #1 ($param) must be greater than or equal to 1', $e->getMessage());
            }
        }
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldFailOnBindParamWithZeroBasedIndex($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_ERRMODE, $pdo::ERRMODE_EXCEPTION);

        $statement = $pdo->prepare('select * from "books" where "id" = ?');

        $id = 7;

        if (PHP_VERSION_ID < 80000) {
            try {
                $statement->bindParam(0, $id);

                $this->fail('Expected exception is not thrown');
            } catch (\PDOException $e) {
                static::assertSame('SQLSTATE[HY093]: Invalid parameter number: Columns/Parameters are 1-based', $e->getMessage());
            }
        } else {
            try {
                $statement->bindParam(0, $id);

                $this->fail('Expected exception is not thrown');
            } catch (\ValueError $e) {
                static::assertSame('PDOStatement::bindParam(): Argument #1 ($param) must be greater than or equal to 1', $e->getMessage());
            }
        }
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldFailOnBindColumnWithZeroBasedIndex($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_ERRMODE, $pdo::ERRMODE_EXCEPTION);

        $statement = $pdo->prepare('select * from "books"');

        if (PHP_VERSION_ID < 80000) {
            try {
                $statement->bindColumn(0, $id);

                $this->fail('Expected exception is not thrown');
            } catch (\PDOException $e) {
                static::assertSame('SQLSTATE[HY093]: Invalid parameter number: Columns/Parameters are 1-based', $e->getMessage());
            }
        } else {
            try {
                $statement->bindColumn(0, $id);

                $this->fail('Expected exception is not thrown');
            } catch (\ValueError $e) {
                static::assertSame('PDOStatement::bindColumn(): Argument #1 ($column) must be greater than or equal to 1', $e->getMessage());
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

        $pdo->exec('create table "books" ("id" integer primary key autoincrement not null, "title" varchar not null, "status" varchar not null, "year" integer not null)');

        $pdo->exec('insert into "books" ("title", "status", "year") values ("Kaidash’s Family", "published", 2024), ("Shadows of the Forgotten Ancestors", "published", 2024)');

        return $pdo;
    }

    /**
     * @return PDOMock
     */
    protected static function configureMock()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = ? and "year" = ?')
            ->toBePrepared()
            ->withParam(1, 'published', $pdo::PARAM_STR)
            ->withParam(2, 2024, $pdo::PARAM_INT)
            ->willFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        return $pdo;
    }
}
