<?php

namespace Tests\Xala\Elomock;

use PDO;
use PDOException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Xala\Elomock\PDOExceptionMock;
use Xala\Elomock\PDOMock;

class ErrorInfoTest extends TestCase
{
    #[Test]
    public function itShouldDisplayErrorInformation(): void
    {
        $scenario = function (PDO $pdo) {
            static::assertNull($pdo->errorCode());
            static::assertSame(['', null, null], $pdo->errorInfo());
        };

        $sqlite = new PDO('sqlite::memory:');
        $scenario($sqlite);

        $mock = new PDOMock();
        $scenario($mock);
    }

    #[Test]
    public function itShouldDisplayErrorInformationForSuccessfullyPreparedStatement(): void
    {
        $scenario = function (PDO $pdo) {
            $statement = $pdo->prepare('insert into "books" ("id", "title") values (1, "Stolen Happiness by Ivan Franko")');

            static::assertNull($statement->errorCode());
            static::assertSame(['', null, null], $statement->errorInfo());
        };

        $sqlite = new PDO('sqlite::memory:');
        $sqlite->exec('create table "books" ("id" integer primary key autoincrement not null, "title" varchar not null)');
        $scenario($sqlite);

        $mock = new PDOMock();
        $scenario($mock);
    }

    #[Test]
    public function itShouldDisplayErrorInformationForSuccessfullyExecutedPreparedStatement(): void
    {
        $scenario = function (PDO $pdo) {
            $statement = $pdo->prepare('insert into "books" ("id", "title") values (1, "Stolen Happiness by Ivan Franko")');

            $statement->execute();

            static::assertSame('00000', $statement->errorCode());
            static::assertSame(['00000', null, null], $statement->errorInfo());
        };

        $sqlite = new PDO('sqlite::memory:');
        $sqlite->exec('create table "books" ("id" integer primary key autoincrement not null, "title" varchar not null)');
        $scenario($sqlite);

        $mock = new PDOMock();
        $mock->expect('insert into "books" ("id", "title") values (1, "Stolen Happiness by Ivan Franko")');
        $scenario($mock);
    }

    #[Test]
    public function itShouldFailWithSyntaxErrorException(): void
    {
        $scenario = function (PDO $pdo) {
            try {
                $pdo->exec('select table "users"');

                $this->fail('Exception was not thrown');
            } catch (PDOException $e) {
                static::assertSame('SQLSTATE[HY000]: General error: 1 near "table": syntax error', $e->getMessage());
                static::assertSame('HY000', $e->getCode());
                static::assertSame(['HY000', 1, 'near "table": syntax error'], $e->errorInfo);
            }
        };

        $sqlite = new PDO('sqlite::memory:');
        $scenario($sqlite);

        $mock = new PDOMock();
        $mock->expect('select table "users"')
            ->andFail(PDOExceptionMock::fromErrorInfo(
                'SQLSTATE[HY000]: General error: 1 near "table": syntax error',
                'HY000',
                'near "table": syntax error',
                1
            ));
        $scenario($mock);
    }

    #[Test]
    public function itShouldFailWithSyntaxErrorExceptionUsingQueryMethod(): void
    {
        $scenario = function (PDO $pdo) {
            try {
                $pdo->query('select table "users"');

                $this->fail('Exception was not thrown');
            } catch (PDOException $e) {
                static::assertSame('SQLSTATE[HY000]: General error: 1 near "table": syntax error', $e->getMessage());
                static::assertSame('HY000', $e->getCode());
                static::assertSame(['HY000', 1, 'near "table": syntax error'], $e->errorInfo);
            }
        };

        $sqlite = new PDO('sqlite::memory:');
        $scenario($sqlite);

        $mock = new PDOMock();
        $mock->expect('select table "users"')
            ->andFail(PDOExceptionMock::fromErrorInfo(
                'SQLSTATE[HY000]: General error: 1 near "table": syntax error',
                'HY000',
                'near "table": syntax error',
                1
            ));
        $scenario($mock);
    }

    #[Test]
    public function itShouldFailWithIntegrityConstraintErrorException(): void
    {
        $scenario = function (PDO $pdo) {
            try {
                $pdo->exec("insert into users (id, name) values (1, null)");

                $this->fail('Exception was not thrown');
            } catch (PDOException $e) {
                static::assertSame('SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: users.name', $e->getMessage());
                static::assertSame('23000', $e->getCode());
                static::assertSame(['23000', 19, 'NOT NULL constraint failed: users.name'], $e->errorInfo);
            }
        };

        $sqlite = new PDO('sqlite::memory:');
        $sqlite->exec("create table users (id integer primary key, name text not null)");
        $scenario($sqlite);

        $mock = new PDOMock();
        $mock->expect('insert into users (id, name) values (1, null)')
            ->andFail(PDOExceptionMock::fromErrorInfo(
                'SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: users.name',
                '23000',
                'NOT NULL constraint failed: users.name',
                19
            ));
        $scenario($mock);
    }

    #[Test]
    public function itShouldDisplayErrorInformationOnFailedQueryUsingPDOInstance(): void
    {
        $scenario = function (PDO $pdo) {
            try {
                $pdo->exec('select table "users"');

                $this->fail('Exception was not thrown');
            } catch (PDOException $e) {
                static::assertSame(['HY000', 1, 'near "table": syntax error'], $pdo->errorInfo());
                static::assertSame('HY000', $pdo->errorCode());
            }
        };

        $sqlite = new PDO('sqlite::memory:');
        $scenario($sqlite);

        $mock = new PDOMock();
        $mock->expect('select table "users"')
            ->andFail(PDOExceptionMock::fromErrorInfo(
                'SQLSTATE[HY000]: General error: 1 near "table": syntax error',
                'HY000',
                'near "table": syntax error',
                1
            ));
        $scenario($mock);
    }

    #[Test]
    public function itShouldFailUsingCustomErrorException(): void
    {
        $mock = new PDOMock();

        $mock->expect('select table "users"')
            ->andFail(new PDOException('Invalid SQL'));

        try {
            $mock->exec('select table "users"');

            $this->fail('Exception was not thrown');
        } catch (PDOException $e) {
            static::assertSame('Invalid SQL', $e->getMessage());
            static::assertSame(0, $e->getCode());
            static::assertNull($e->errorInfo);
        }
    }

    // TODO: test different error modes (silent, etc)
    // TODO: test errorCode and errorInfo on PDOStatement instance
    // TODO: handle exception during "prepare" execution
}
