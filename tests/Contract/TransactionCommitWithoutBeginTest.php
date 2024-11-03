<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use PDOException;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class TransactionCommitWithoutBeginTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     */
    public function itShouldFailOnCommitWithoutBeginTransaction($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_ERRMODE, $pdo::ERRMODE_SILENT);

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('There is no active transaction');

        $pdo->commit();
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
        $pdo = new PDOMock();

        $pdo->expectCommit();

        return $pdo;
    }
}
