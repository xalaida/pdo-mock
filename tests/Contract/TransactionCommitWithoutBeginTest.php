<?php

namespace Tests\Xala\Elomock\Contract;

use PDO;
use PDOException;
use Tests\Xala\Elomock\TestCase;
use Xala\Elomock\PDOMock;

class TransactionCommitWithoutBeginTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     */
    public function itShouldFailOnCommitWithoutBeginTransaction(PDO $pdo)
    {
        $pdo->setAttribute($pdo::ATTR_ERRMODE, $pdo::ERRMODE_SILENT);

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('There is no active transaction');

        $pdo->commit();
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
        $pdo = new PDOMock();

        $pdo->expectCommit();

        return $pdo;
    }
}
