<?php

namespace Tests\Xala\Elomock\Mirror;

use PDO;
use PDOException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Xala\Elomock\PDOMock;

class TransactionRollbackWithoutBeginTest extends TestCase
{
    #[Test]
    #[DataProvider('connections')]
    public function itShouldFailOnCRollbackWithoutBeginTransaction(PDO $pdo): void
    {
        $pdo->setAttribute($pdo::ATTR_ERRMODE, $pdo::ERRMODE_SILENT);

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('There is no active transaction');

        $pdo->rollBack();
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
        $pdo = new PDOMock();

        $pdo->expectRollback();

        return $pdo;
    }
}
