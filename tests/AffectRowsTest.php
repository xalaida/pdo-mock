<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Xala\Elomock\FakePDO;

class AffectRowsTest extends TestCase
{
    #[Test]
    public function itShouldHandleAffectRows(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('insert into "users" ("name") values ("john"), ("jane")')
            ->affectRows(2);

        $result = $pdo->exec('insert into "users" ("name") values ("john"), ("jane")');

        static::assertSame(2, $result);
    }
}
