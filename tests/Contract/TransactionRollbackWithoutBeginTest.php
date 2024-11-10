<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use PDOException;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class TransactionRollbackWithoutBeginTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldFailOnCRollbackWithoutBeginTransaction($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_ERRMODE, $pdo::ERRMODE_SILENT);

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('There is no active transaction');

        $pdo->rollBack();
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
        $pdo = new PDOMock();

        $pdo->expectRollback();

        return $pdo;
    }
}
