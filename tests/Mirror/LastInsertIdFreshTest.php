<?php

namespace Tests\Xala\Elomock\Mirror;

use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Xala\Elomock\PDOMock;

class LastInsertIdFreshTest extends TestCase
{
    #[Test]
    #[DataProvider('connections')]
    public function itShouldReturnZeroAsLastInsertId(PDO $pdo): void
    {
        static::assertSame('0', $pdo->lastInsertId());
        static::assertSame('0', $pdo->lastInsertId());
    }

    public static function connections(): array
    {
        return [
            'SQLite' => [
                static::prepareSqlite()
            ],

            'Mock' => [
                static::prepareMock()
            ],
        ];
    }

    protected static function prepareSqlite(): PDO
    {
        return new PDO('sqlite::memory:');
    }

    protected static function prepareMock(): PDOMock
    {
        return new PDOMock();
    }
}
