<?php

use PHPUnit\Framework\Attributes\Test;
use Tests\Xala\Elomock\TestCase;
use Xala\Elomock\PDO\FakePDO;

class FakePDOTest extends TestCase
{
    #[Test]
    public function itShouldHandleQuery(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('DELETE * FROM "users"');

        $result = $pdo->exec('DELETE * FROM "users"');

        $this->assertEquals(1, $result);
    }
}
