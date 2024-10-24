<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Xala\Elomock\FakePDO;

class LastInsertIdTest extends TestCase
{
    #[Test]
    public function itShouldReturnZeroAsLastInsertId(): void
    {
        $pdo = new FakePDO();

        static::assertSame('0', $pdo->lastInsertId());
        static::assertSame('0', $pdo->lastInsertId());
    }

    #[Test]
    public function itShouldUseLastInsertIdFromQuery(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('insert into "users" ("name") values ("john")')
            ->withInsertId(777);

        $pdo->exec('insert into "users" ("name") values ("john")');

        static::assertSame('777', $pdo->lastInsertId());
        static::assertSame('777', $pdo->lastInsertId());
    }
}
