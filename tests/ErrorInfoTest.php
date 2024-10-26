<?php

namespace Tests\Xala\Elomock;

use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Xala\Elomock\PDOMock;

class ErrorInfoTest extends TestCase
{
    #[Test]
    public function itShouldDisplayErrorInformation(): void
    {
        $errorInfo = function (PDO $pdo) {
            return $pdo->errorInfo();
        };

        static::assertEquals($errorInfo($this->sqlite()), $errorInfo(new PDOMock()));
    }

    #[Test]
    public function itShouldDisplayErrorCode(): void
    {
        $errorCode = function (PDO $pdo) {
            return $pdo->errorCode();
        };

        static::assertEquals($errorCode($this->sqlite()), $errorCode(new PDOMock()));
    }

    #[Test]
    public function itShouldDisplayErrorInformationForSuccessfullyPreparedStatement(): void
    {
        $mock = new PDOMock();
        $mock->expect('insert into "books" ("id", "title") values (1, "Stolen Happiness by Ivan Franko")');

        $errorInfo = function (PDO $pdo) {
            $statement = $pdo->prepare('insert into "books" ("id", "title") values (1, "Stolen Happiness by Ivan Franko")');

            return $statement->errorInfo();
        };

        static::assertEquals($errorInfo($this->sqlite()), $errorInfo($mock));
    }

    #[Test]
    public function itShouldDisplayErrorInformationForSuccessfullyExecutedPreparedStatement(): void
    {
        $mock = new PDOMock();
        $mock->expect('insert into "books" ("id", "title") values (1, "Stolen Happiness by Ivan Franko")');

        $errorInfo = function (PDO $pdo) {
            $statement = $pdo->prepare('insert into "books" ("id", "title") values (1, "Stolen Happiness by Ivan Franko")');

            $statement->execute();

            return $statement->errorInfo();
        };

        static::assertEquals($errorInfo($this->sqlite()), $errorInfo($mock));
    }

    #[Test]
    public function itShouldDisplayErrorCodeForSuccessfullyPreparedStatement(): void
    {
        $mock = new PDOMock();
        $mock->expect('insert into "books" ("id", "title") values (1, "Stolen Happiness by Ivan Franko")');

        $errorCode = function (PDO $pdo) {
            $statement = $pdo->prepare('insert into "books" ("id", "title") values (1, "Stolen Happiness by Ivan Franko")');

            return $statement->errorCode();
        };

        static::assertSame($errorCode($this->sqlite()), $errorCode($mock));
    }

    #[Test]
    public function itShouldDisplayErrorCodeForSuccessfullyExecutedPreparedStatement(): void
    {
        $mock = new PDOMock();
        $mock->expect('insert into "books" ("id", "title") values (1, "Stolen Happiness by Ivan Franko")');

        $errorCode = function (PDO $pdo) {
            $statement = $pdo->prepare('insert into "books" ("id", "title") values (1, "Stolen Happiness by Ivan Franko")');

            $statement->execute();

            return $statement->errorCode();
        };

        static::assertSame($errorCode($this->sqlite()), $errorCode($mock));
    }

    protected function sqlite(): PDO
    {
        $sqlite = new PDO('sqlite::memory:');

        $sqlite->exec('create table "books" ("id" integer primary key autoincrement not null, "title" varchar not null)');

        return $sqlite;
    }
}
