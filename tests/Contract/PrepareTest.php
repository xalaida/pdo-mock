<?php

namespace Tests\Xala\Elomock\Contract;

use PDO;
use PHPUnit\Framework\Attributes\Test;
use Xala\Elomock\PDOMock;

class PrepareTest extends TestCase
{
    #[Test]
    public function itShouldHandlePreparedStatement(): void
    {
        $scenario = function (PDO $pdo) {
            $statement = $pdo->prepare('select * from "books"');

            $result = $statement->execute();

            static::assertTrue($result);
        };

        $scenario($this->sqlite());

        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared();

        $scenario($pdo);

        $pdo->assertExpectationsFulfilled();
    }

    #[Test]
    public function itShouldHandleBindValue(): void
    {
        $scenario = function (PDO $pdo) {
            $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ?');

            static::assertTrue(
                $statement->bindValue(1, 'active', $pdo::PARAM_STR)
            );

            static::assertTrue(
                $statement->bindValue(2, 2024, $pdo::PARAM_INT)
            );

            static::assertTrue(
                $statement->execute()
            );
        };

        $scenario($this->sqlite());

        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = ? and "year" = ?')
            ->toBePrepared()
            ->withBound(1, 'active', $pdo::PARAM_STR)
            ->withBound(2, 2024, $pdo::PARAM_INT);

        $scenario($pdo);
    }

    #[Test]
    public function itShouldHandleBindParam(): void
    {
        $scenario = function (PDO $pdo) {
            $status = 'published';
            $year = 2024;

            $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ?');

            static::assertTrue(
                $statement->bindParam(1, $status, $pdo::PARAM_STR, 10)
            );

            static::assertTrue(
                $statement->bindParam(2, $year, $pdo::PARAM_INT)
            );

            static::assertTrue(
                $statement->execute()
            );
        };

        $scenario($this->sqlite());

        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = ? and "year" = ?')
            ->toBePrepared()
            ->withBound(1, 'published', $pdo::PARAM_STR)
            ->withBound(2, 2024, $pdo::PARAM_INT);

        $scenario($pdo);
    }
}
