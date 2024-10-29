<?php

namespace Tests\Xala\Elomock\Contract;

use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Xala\Elomock\TestCase;
use Xala\Elomock\PDOMock;

class ErrorInfoFreshTest extends TestCase
{
    #[Test]
    #[DataProvider('contracts')]
    public function itShouldDisplayErrorInformationForPDOInstance(PDO $pdo): void
    {
        static::assertNull($pdo->errorCode());
        static::assertSame(['', null, null], $pdo->errorInfo());
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
        return new PDO('sqlite::memory:');
    }

    protected static function configureMock(): PDOMock
    {
        return new PDOMock();
    }
}
