<?php

namespace Tests\Xala\Elomock;

use PDO;
use PDOException;
use PHPUnit\Framework\Attributes\Test;
use Xala\Elomock\PDOExceptionMock;
use Xala\Elomock\PDOMock;

class ErrorInfoTest extends TestCase
{
    #[Test]
    public function itShouldDisplayErrorInformationForPDOInstance(): void
    {
        $scenario = function (PDO $pdo) {
            static::assertNull($pdo->errorCode());
            static::assertSame(['', null, null], $pdo->errorInfo());
        };

        $scenario(new PDO('sqlite::memory:'));

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

            static::assertSame('00000', $pdo->errorCode());
            static::assertSame(['00000', null, null], $pdo->errorInfo());
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();
        $mock->expect('insert into "books" ("id", "title") values (1, "Stolen Happiness by Ivan Franko")');
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

            static::assertSame('00000', $pdo->errorCode());
            static::assertSame(['00000', null, null], $pdo->errorInfo());
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();
        $mock->expect('insert into "books" ("id", "title") values (1, "Stolen Happiness by Ivan Franko")');
        $scenario($mock);
    }

    #[Test]
    public function itShouldFailWithSyntaxErrorException(): void
    {
        $scenario = function (PDO $pdo) {
            try {
                $pdo->exec('select table "books"');

                $this->fail('Exception was not thrown');
            } catch (PDOException $e) {
                static::assertSame('SQLSTATE[HY000]: General error: 1 near "table": syntax error', $e->getMessage());
                static::assertSame('HY000', $e->getCode());
                static::assertSame(['HY000', 1, 'near "table": syntax error'], $e->errorInfo);

                static::assertSame(['HY000', 1, 'near "table": syntax error'], $pdo->errorInfo());
                static::assertSame('HY000', $pdo->errorCode());
            }
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();
        $mock->expect('select table "books"')
            ->andFailOnExecute(PDOExceptionMock::fromErrorInfo(
                'SQLSTATE[HY000]: General error: 1 near "table": syntax error',
                'HY000',
                'near "table": syntax error',
                1
            ));
        $scenario($mock);
    }

    #[Test]
    public function itShouldFailWithSyntaxErrorExceptionOnPrepare(): void
    {
        $scenario = function (PDO $pdo) {
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
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();
        $mock->expect('select table "books"')
            ->andFailOnPrepare(PDOExceptionMock::fromErrorInfo(
                'SQLSTATE[HY000]: General error: 1 near "table": syntax error',
                'HY000',
                'near "table": syntax error',
                1
            ));
        $scenario($mock);
    }

    #[Test]
    public function itShouldFailWithIntegrityConstraintErrorExceptionUsingPreparedStatement(): void
    {
        $scenario = function (PDO $pdo) {
            $statement = $pdo->prepare("insert into books (id, title) values (1, null)");

            try {
                $statement->execute();

                $this->fail('Exception was not thrown');
            } catch (PDOException $e) {
                static::assertSame('SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: books.title', $e->getMessage());
                static::assertSame('23000', $e->getCode());
                static::assertSame(['23000', 19, 'NOT NULL constraint failed: books.title'], $e->errorInfo);

                static::assertSame(['23000', 19, 'NOT NULL constraint failed: books.title'], $statement->errorInfo());
                static::assertSame('23000', $statement->errorCode());

                static::assertSame(['00000', null, null], $pdo->errorInfo());
                static::assertSame('00000', $pdo->errorCode());
            }
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();
        $mock->expect('insert into books (id, title) values (1, null)')
            ->andFailOnExecute(PDOExceptionMock::fromErrorInfo(
                'SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: books.title',
                '23000',
                'NOT NULL constraint failed: books.title',
                19
            ));
        $scenario($mock);
    }

    #[Test]
    public function itShouldFailUsingCustomErrorException(): void
    {
        $mock = new PDOMock();

        $mock->expect('select table "books"')
            ->andFailOnExecute(new PDOException('Invalid SQL'));

        try {
            $mock->exec('select table "books"');

            $this->fail('Exception was not thrown');
        } catch (PDOException $e) {
            static::assertSame('Invalid SQL', $e->getMessage());
            static::assertSame(0, $e->getCode());
            static::assertNull($e->errorInfo);
        }
    }

    #[Test]
    public function itShouldClearPreviousErrorInfoOnSuccessfulQuery(): void
    {
        $scenario = function (PDO $pdo) {
            try {
                $pdo->exec('select table "books"');

                $this->fail('Exception was not thrown');
            } catch (PDOException $e) {
                static::assertSame('SQLSTATE[HY000]: General error: 1 near "table": syntax error', $e->getMessage());
            }

            $pdo->exec('select * from "books"');

            static::assertSame(['00000', null, null], $pdo->errorInfo());
            static::assertSame('00000', $pdo->errorCode());
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();
        $mock->expect('select table "books"')
            ->andFailOnExecute(PDOExceptionMock::fromErrorInfo(
                'SQLSTATE[HY000]: General error: 1 near "table": syntax error',
                'HY000',
                'near "table": syntax error',
                1
            ));
        $mock->expect('select * from "books"');
        $scenario($mock);
    }

    // TODO: test different error modes (silent, etc)

    #[Test]
    public function itShouldFailWithSyntaxErrorUsingSilentErrorMode(): void
    {
        $scenario = function (PDO $pdo) {
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

            $result = $pdo->exec('select table "books"');

            static::assertFalse($result);
            static::assertSame(['HY000', 1, 'near "table": syntax error'], $pdo->errorInfo());
            static::assertSame('HY000', $pdo->errorCode());
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();
        $mock->expect('select table "books"')
            ->andFailOnExecute(PDOExceptionMock::fromErrorInfo(
                'SQLSTATE[HY000]: General error: 1 near "table": syntax error',
                'HY000',
                'near "table": syntax error',
                1
            ));
        $scenario($mock);
    }

    #[Test]
    public function itShouldFailWithSyntaxErrorUsingWarningErrorMode(): void
    {
        $scenario = function (PDO $pdo) {
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

            $result = $this->expectTriggerWarning(function () use ($pdo) {
                return $pdo->exec('select table "books"');
            }, 'PDO::exec(): SQLSTATE[HY000]: General error: 1 near "table": syntax error');

            static::assertFalse($result);
            static::assertSame(['HY000', 1, 'near "table": syntax error'], $pdo->errorInfo());
            static::assertSame('HY000', $pdo->errorCode());
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();
        $mock->expect('select table "books"')
            ->andFailOnExecute(PDOExceptionMock::fromErrorInfo(
                'SQLSTATE[HY000]: General error: 1 near "table": syntax error',
                'HY000',
                'near "table": syntax error',
                1
            ));
        $scenario($mock);
    }

    #[Test]
    public function itShouldFailWithSyntaxErrorOnPrepareUsingWarningErrorMode(): void
    {
        $scenario = function (PDO $pdo) {
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

            $result = $this->expectTriggerWarning(function () use ($pdo) {
                return $pdo->prepare('select table "books"');
            }, 'PDO::prepare(): SQLSTATE[HY000]: General error: 1 near "table": syntax error');

            static::assertFalse($result);

            static::assertSame(['HY000', 1, 'near "table": syntax error'], $pdo->errorInfo());
            static::assertSame('HY000', $pdo->errorCode());
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();
        $mock->expect('select table "books"')
            ->andFailOnPrepare(PDOExceptionMock::fromErrorInfo(
                'SQLSTATE[HY000]: General error: 1 near "table": syntax error',
                'HY000',
                'near "table": syntax error',
                1
            ));
        $scenario($mock);
    }

    #[Test]
    public function itShouldFailWithSyntaxErrorOnExecuteForPreparedStatementUsingWarningErrorMode(): void
    {
        $scenario = function (PDO $pdo) {
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

            $statement = $pdo->prepare('insert into books (id, title) values (1, null)');

            $result = $this->expectTriggerWarning(function () use ($statement) {
                return $statement->execute();
            }, 'PDOStatement::execute(): SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: books.title');

            static::assertFalse($result);

            static::assertSame(['23000', 19, 'NOT NULL constraint failed: books.title'], $statement->errorInfo());
            static::assertSame('23000', $statement->errorCode());

            static::assertSame(['00000', null, null], $pdo->errorInfo());
            static::assertSame('00000', $pdo->errorCode());
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();
        $mock->expect('insert into books (id, title) values (1, null)')
            ->andFailOnExecute(PDOExceptionMock::fromErrorInfo(
                'SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: books.title',
                '23000',
                'NOT NULL constraint failed: books.title',
                19
            ));
        $scenario($mock);
    }
}
