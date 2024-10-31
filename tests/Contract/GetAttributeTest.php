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
     */
    public function itShouldReturnAttributeAutocommit($pdo)
    {
        $this->expectExceptionMessage('SQLSTATE[IM001]: Driver does not support this function: driver does not support that attribute');

        $pdo->getAttribute($pdo::ATTR_AUTOCOMMIT);
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldReturnAttributePrefetch($pdo)
    {
        $this->expectExceptionMessage('SQLSTATE[IM001]: Driver does not support this function: driver does not support that attribute');

        $pdo->getAttribute($pdo::ATTR_PREFETCH);
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldReturnAttributeTimeout($pdo)
    {
        $this->expectExceptionMessage('SQLSTATE[IM001]: Driver does not support this function: driver does not support that attribute');

        $pdo->getAttribute($pdo::ATTR_TIMEOUT);
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
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
     */
    public function itShouldReturnAttributeServerVersion($pdo)
    {
        static::assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', $pdo->getAttribute($pdo::ATTR_SERVER_VERSION));
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldReturnAttributeClientVersion($pdo)
    {
        static::assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', $pdo->getAttribute($pdo::ATTR_CLIENT_VERSION));
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldReturnAttributeServerInfo($pdo)
    {
        $this->expectExceptionMessage('SQLSTATE[IM001]: Driver does not support this function: driver does not support that attribute');

        $pdo->getAttribute($pdo::ATTR_SERVER_INFO);
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldReturnAttributeConnectionStatus($pdo)
    {
        $this->expectExceptionMessage('SQLSTATE[IM001]: Driver does not support this function: driver does not support that attribute');

        $pdo->getAttribute($pdo::ATTR_CONNECTION_STATUS);
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldReturnAttributeCase($pdo)
    {
        static::assertSame($pdo::CASE_NATURAL, $pdo->getAttribute($pdo::ATTR_CASE));
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldReturnAttributeCursorName($pdo)
    {
        $this->expectExceptionMessage('SQLSTATE[IM001]: Driver does not support this function: driver does not support that attribute');

        $pdo->getAttribute($pdo::ATTR_CURSOR_NAME);
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldReturnAttributeCursor($pdo)
    {
        $this->expectExceptionMessage('SQLSTATE[IM001]: Driver does not support this function: driver does not support that attribute');

        $pdo->getAttribute($pdo::ATTR_CURSOR);
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldReturnAttributeOracleNulls($pdo)
    {
        static::assertSame(0, $pdo->getAttribute($pdo::ATTR_ORACLE_NULLS));
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldReturnAttributePersistent($pdo)
    {
        static::assertSame(false, $pdo->getAttribute($pdo::ATTR_PERSISTENT));
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldReturnAttributeStatementClass($pdo)
    {
        static::assertSame([PDOStatement::class], $pdo->getAttribute($pdo::ATTR_STATEMENT_CLASS));
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldReturnAttributeFetchTableNames($pdo)
    {
        $this->expectExceptionMessage('SQLSTATE[IM001]: Driver does not support this function: driver does not support that attribute');

        $pdo->getAttribute($pdo::ATTR_FETCH_TABLE_NAMES);
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldReturnAttributeFetchCatalogNames($pdo)
    {
        $this->expectExceptionMessage('SQLSTATE[IM001]: Driver does not support this function: driver does not support that attribute');

        $pdo->getAttribute($pdo::ATTR_FETCH_CATALOG_NAMES);
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldReturnAttributeDriverName($pdo)
    {
        static::assertSame('sqlite', $pdo->getAttribute($pdo::ATTR_DRIVER_NAME));
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldReturnAttributeStringifyFetches($pdo)
    {
        if (PHP_VERSION_ID < 80200) {
            $this->expectExceptionMessage('SQLSTATE[IM001]: Driver does not support this function: driver does not support that attribute');

            $pdo->getAttribute($pdo::ATTR_STRINGIFY_FETCHES);
        } else {
            static::assertSame(false, $pdo->getAttribute($pdo::ATTR_STRINGIFY_FETCHES));
        }
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldReturnAttributeMaxColumnLen($pdo)
    {
        $this->expectExceptionMessage('SQLSTATE[IM001]: Driver does not support this function: driver does not support that attribute');

        $pdo->getAttribute($pdo::ATTR_MAX_COLUMN_LEN);
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldReturnAttributeEmulatePrepares($pdo)
    {
        $this->expectExceptionMessage('SQLSTATE[IM001]: Driver does not support this function: driver does not support that attribute');

        $pdo->getAttribute($pdo::ATTR_EMULATE_PREPARES);
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldReturnAttributeDefaultFetchMode($pdo)
    {
        static::assertSame($pdo::FETCH_BOTH, $pdo->getAttribute($pdo::ATTR_DEFAULT_FETCH_MODE));
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
        return new PDO('sqlite::memory:');
    }

    protected static function configureMock()
    {
        return new PDOMock('sqlite');
    }
}
