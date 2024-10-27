<?php

namespace Tests\Xala\Elomock;

use PDO;
use PHPUnit\Framework\Attributes\Test;
use Xala\Elomock\PDOMock;

class LastInsertIdTest extends TestCase
{
    #[Test]
    public function itShouldReturnZeroAsLastInsertId(): void
    {
        $scenario = function (PDO $pdo) {
            static::assertSame('0', $pdo->lastInsertId());
            static::assertSame('0', $pdo->lastInsertId());
        };

        $scenario($this->sqlite());

        $scenario(new PDOMock());
    }

    #[Test]
    public function itShouldUseLastInsertIdFromQuery(): void
    {
        $scenario = function (PDO $pdo) {
            $pdo->exec('insert into "books" ("id", "title") values (777, "Kaidash’s Family")');

            static::assertSame('777', $pdo->lastInsertId());
            static::assertSame('777', $pdo->lastInsertId());
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();
        $mock->expect('insert into "books" ("id", "title") values (777, "Kaidash’s Family")')->withInsertId(777);
        $scenario($mock);
    }
}
