<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use PDOStatement;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class GetAttributeTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldReturnAttributeErrorMode($pdo)
    {
        if (PHP_VERSION_ID < 80000) {
            static::assertSame($pdo::ERRMODE_SILENT, $pdo->getAttribute($pdo::ATTR_ERRMODE));
        } else {
            static::assertSame($pdo::ERRMODE_EXCEPTION, $pdo->getAttribute($pdo::ATTR_ERRMODE));
        }
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldReturnAttributeCase($pdo)
    {
        static::assertSame($pdo::CASE_NATURAL, $pdo->getAttribute($pdo::ATTR_CASE));
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldReturnAttributeOracleNulls($pdo)
    {
        static::assertSame(0, $pdo->getAttribute($pdo::ATTR_ORACLE_NULLS));
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldReturnAttributeStatementClass($pdo)
    {
        static::assertSame([PDOStatement::class], $pdo->getAttribute($pdo::ATTR_STATEMENT_CLASS));
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldReturnAttributeDefaultFetchMode($pdo)
    {
        static::assertSame($pdo::FETCH_BOTH, $pdo->getAttribute($pdo::ATTR_DEFAULT_FETCH_MODE));
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
        return new PDO('sqlite::memory:');
    }

    /**
     * @return PDOMock
     */
    protected static function configureMock()
    {
        return new PDOMock();
    }
}
