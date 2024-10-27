<?php

namespace Tests\Xala\Elomock\Mirror;

use PDO;
use PHPUnit\Framework\Attributes\Test;
use Xala\Elomock\PDOMock;

class AffectRowsTest extends TestCase
{
    #[Test]
    public function itShouldReturnAffectedRows(): void
    {
        $scenario = function (PDO $pdo) {
            $result = $pdo->exec('delete from "books"');

            static::assertSame(0, $result);
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();
        $mock->expect('delete from "books"');
        $scenario($mock);
    }

    #[Test]
    public function itShouldReturnSpecifiedAffectedRows(): void
    {
        $scenario = function (PDO $pdo) {
            $result = $pdo->exec('insert into "books" ("title") values ("Shadows of the Forgotten Ancestors")');

            static::assertSame(1, $result);
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();
        $mock->expect('insert into "books" ("title") values ("Shadows of the Forgotten Ancestors")')->affecting(1);
        $scenario($mock);
    }

    #[Test]
    public function itShouldReturnRowCountUsingNotExecutedPreparedStatement(): void
    {
        $scenario = function (PDO $pdo) {
            $statement = $pdo->prepare('delete from "books"');

            static::assertSame(0, $statement->rowCount());
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();
        $mock->expect('delete from "books"');
        $scenario($mock);
    }

    #[Test]
    public function itShouldIgnoreSpecifiedAffectedRowsUsingNotExecutedPreparedStatement(): void
    {
        $scenario = function (PDO $pdo) {
            $statement = $pdo->prepare('delete from "books"');

            static::assertSame(0, $statement->rowCount());
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();
        $mock->expect('delete from "books"')->affecting(2);
        $scenario($mock);
    }

    #[Test]
    public function itShouldReturnAffectedRowsUsingPreparedStatement(): void
    {
        $scenario = function (PDO $pdo) {
            $statement = $pdo->prepare('insert into "books" ("title") values ("Shadows of the Forgotten Ancestors"), ("Kaidash’s Family")');

            $statement->execute();

            static::assertSame(2, $statement->rowCount());
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();
        $mock->expect('insert into "books" ("title") values ("Shadows of the Forgotten Ancestors"), ("Kaidash’s Family")')->affecting(2);
        $scenario($mock);
    }
}
