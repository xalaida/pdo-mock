<?php

namespace Tests\Xala\Elomock\Mirror;

use PDO;
use PHPUnit\Framework\Attributes\Test;
use Xala\Elomock\PDOMock;

class PreparedStatementBindingsTest extends TestCase
{
    #[Test]
    public function itShouldHandleQueryBindings(): void
    {
        $scenario = function (PDO $pdo) {
            $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ? and "published" = ?');

            $statement->bindValue(1, 'active', $pdo::PARAM_STR);
            $statement->bindValue(2, 2024, $pdo::PARAM_INT);
            $statement->bindValue(3, true, $pdo::PARAM_BOOL);

            $result = $statement->execute();

            static::assertTrue($result);
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();

        $mock->expect('select * from "books" where "status" = ? and "year" = ? and "published" = ?')
            ->toBePrepared()
            ->withBinding(1, 'active', $mock::PARAM_STR)
            ->withBinding(2, 2024, $mock::PARAM_INT)
            ->withBinding(3, true, $mock::PARAM_BOOL);

        $scenario($mock);
    }

    #[Test]
    public function itShouldHandleBindingsAsOptional(): void
    {
        $scenario = function (PDO $pdo) {
            $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ? and "published" = ?');

            $statement->bindValue(1, 'active', $pdo::PARAM_STR);
            $statement->bindValue(2, 2024, $pdo::PARAM_INT);
            $statement->bindValue(3, true, $pdo::PARAM_BOOL);

            $result = $statement->execute();

            static::assertTrue($result);
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();
        $mock->expect('select * from "books" where "status" = ? and "year" = ? and "published" = ?')->toBePrepared();
        $scenario($mock);
    }
}
