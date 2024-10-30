<?php

namespace Tests\Xala\Elomock\Contract;

use PDO;
use PDOException;
use Tests\Xala\Elomock\TestCase;
use Xala\Elomock\PDOExceptionMock;
use Xala\Elomock\PDOMock;

class ErrorInfoPrepareInvalidSyntaxTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     */
    public function itShouldFailWithSyntaxErrorExceptionOnPrepare(PDO $pdo)
    {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $pdo->prepare('select table "books"');

            $this->fail('Exception was not thrown');
        } catch (PDOException $e) {
            static::assertSame('SQLSTATE[HY000]: General error: 1 near "table": syntax error', $e->getMessage());
            static::assertSame('HY000', $e->getCode());
            static::assertSame(['HY000', 1, 'near "table": syntax error'], $e->errorInfo);

            static::assertSame(['HY000', 1, 'near "table": syntax error'], $pdo->errorInfo());
            static::assertSame('HY000', $pdo->errorCode());
        }
    }

    /**
     * @test
     * @dataProvider contracts
     */
    public function itShouldFailWithSyntaxErrorOnPrepareUsingWarningErrorMode(PDO $pdo)
    {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

        $result = $this->expectTriggerWarning(function () use ($pdo) {
            return $pdo->prepare('select table "books"');
        }, 'PDO::prepare(): SQLSTATE[HY000]: General error: 1 near "table": syntax error');

        static::assertFalse($result);

        static::assertSame(['HY000', 1, 'near "table": syntax error'], $pdo->errorInfo());
        static::assertSame('HY000', $pdo->errorCode());
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
        $pdo = new PDO('sqlite::memory:');

        $pdo->exec('create table "books" ("id" integer primary key autoincrement not null, "title" varchar not null)');

        return $pdo;
    }

    protected static function configureMock(): PDOMock
    {
        $pdo = new PDOMock();

        $pdo->expect('select table "books"')
            ->andFailOnPrepare(PDOExceptionMock::fromErrorInfo(
                'SQLSTATE[HY000]: General error: 1 near "table": syntax error',
                'HY000',
                'near "table": syntax error',
                1
            ));

        return $pdo;
    }
}
