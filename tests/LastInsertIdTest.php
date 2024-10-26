<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Xala\Elomock\PDOMock;

class LastInsertIdTest extends TestCase
{
    #[Test]
    public function itShouldReturnZeroAsLastInsertId(): void
    {
        $pdo = new PDOMock();

        static::assertSame('0', $pdo->lastInsertId());
        static::assertSame('0', $pdo->lastInsertId());
    }

    #[Test]
    public function itShouldUseLastInsertIdFromQuery(): void
    {
        $pdo = new PDOMock();

        $pdo->expectQuery('insert into "users" ("name") values ("john")')
            ->withInsertId(777);

        $pdo->exec('insert into "users" ("name") values ("john")');

        static::assertSame('777', $pdo->lastInsertId());
        static::assertSame('777', $pdo->lastInsertId());
    }
}
