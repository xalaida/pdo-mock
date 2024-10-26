<?php

namespace Tests\Xala\Elomock;

use PDOException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Xala\Elomock\PDOMock;

class ExceptionTest extends TestCase
{
    #[Test]
    public function itShouldHandleQueryException(): void
    {
        $pdo = new PDOMock();

        $pdo->expectQuery('select table "users"')
            ->andFail('SQL syntax error');

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('SQL syntax error');

        $pdo->exec('select table "users"');
    }
}
