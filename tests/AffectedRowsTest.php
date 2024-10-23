<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Xala\Elomock\FakePDO;

class AffectedRowsTest extends TestCase
{
    #[Test]
    public function itShouldReturnZeroAffectedRowsByDefault(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('insert into "users" ("name") values ("john")');

        $result = $pdo->exec('insert into "users" ("name") values ("john")');

        static::assertSame(0, $result);
    }

    #[Test]
    public function itShouldHandleAffectedRows(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('insert into "users" ("name") values ("john"), ("jane")')
            ->affectRows(2);

        $result = $pdo->exec('insert into "users" ("name") values ("john"), ("jane")');

        static::assertSame(2, $result);
    }
}
