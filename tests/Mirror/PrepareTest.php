<?php

namespace Tests\Xala\Elomock\Mirror;

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

        $mock = new PDOMock();

        $mock->expect('select * from "books"')
            ->toBePrepared();

        $scenario($mock);

        $mock->assertExpectationsFulfilled();
    }

    #[Test]
    public function itShouldHandleBindValues(): void
    {
        $scenario = function (PDO $pdo) {
            $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ? and "published" = ?');

            static::assertTrue(
                $statement->bindValue(1, 'active', $pdo::PARAM_STR)
            );

            static::assertTrue(
                $statement->bindValue(2, 2024, $pdo::PARAM_INT)
            );

            static::assertTrue(
                $statement->bindValue(3, true, $pdo::PARAM_BOOL)
            );

            static::assertTrue(
                $statement->execute()
            );
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();

        $mock->expect('select * from "books" where "status" = ? and "year" = ? and "published" = ?')
            ->toBePrepared()
            ->toBindValue(1, 'active', $mock::PARAM_STR)
            ->toBindValue(2, 2024, $mock::PARAM_INT)
            ->toBindValue(3, true, $mock::PARAM_BOOL);

        $scenario($mock);
    }
}
