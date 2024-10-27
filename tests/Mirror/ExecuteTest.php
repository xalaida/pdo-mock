<?php

namespace Tests\Xala\Elomock\Mirror;

use PDO;
use PHPUnit\Framework\Attributes\Test;
use Xala\Elomock\PDOMock;

class ExecuteTest extends TestCase
{
    #[Test]
    public function itShouldExecuteQuery(): void
    {
        $scenario = function (PDO $pdo) {
            $result = $pdo->exec('select * from "books"');

            static::assertSame(0, $result);
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();
        $mock->expect('select * from "books"');
        $scenario($mock);
    }

    #[Test]
    public function itShouldReturnAffectedRowsOnExecute(): void
    {
        $scenario = function (PDO $pdo) {
            $result = $pdo->exec('insert into "books" ("title") values ("Shadows of the Forgotten Ancestors"), ("Kaidash’s Family")');

            static::assertSame(2, $result);
        };

        $scenario($this->sqlite());

        $mock = new PDOMock();
        $mock->expect('insert into "books" ("title") values ("Shadows of the Forgotten Ancestors"), ("Kaidash’s Family")')
            ->affecting(2);
        $scenario($mock);
    }
}
