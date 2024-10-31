<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class GetAttributeTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldReturnErrorModeAttribute($pdo)
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
    public function itShouldReturnStringifyFetchesAttribute($pdo)
    {
        if (PHP_VERSION_ID < 80200) {
            $this->markTestSkipped('PHP internal bug: https://github.com/php/php-src/issues/12969');
        } else {
            static::assertSame(false, $pdo->getAttribute($pdo::ATTR_STRINGIFY_FETCHES));
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
        return new PDO('sqlite::memory:');
    }

    protected static function configureMock()
    {
        return new PDOMock('sqlite');
    }
}
