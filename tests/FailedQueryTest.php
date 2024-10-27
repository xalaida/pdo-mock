<?php

namespace Tests\Xala\Elomock;

use PDOException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Xala\Elomock\PDOExceptionMock;
use Xala\Elomock\PDOMock;

class FailedQueryTest extends TestCase
{
    #[Test]
    public function itShouldFailWithQueryException(): void
    {
        $pdo = new PDOMock();
        $pdo->expect('insert into "books" ("id", "title") values (1, null)')
            ->andFailOnExecute(PDOExceptionMock::fromErrorInfo(
                'Query exception',
                '000',
                'Invalid syntax',
                1
            ));

        $statement = $pdo->prepare('insert into "books" ("id", "title") values (1, null)');

        try {
            $statement->execute();

            $this->fail('Exception was not thrown');
        } catch (PDOException $e) {
            static::assertSame('Query exception', $e->getMessage());
            static::assertSame('000', $e->getCode());
            static::assertSame(['000', 1, 'Invalid syntax'], $e->errorInfo);

            static::assertSame(['000', 1, 'Invalid syntax'], $statement->errorInfo());
            static::assertSame('000', $statement->errorCode());

            static::assertSame(['00000', null, null], $pdo->errorInfo());
            static::assertSame('00000', $pdo->errorCode());
        }
    }

    #[Test]
    public function itShouldFailUsingCustomErrorException(): void
    {
        $mock = new PDOMock();

        $mock->expect('select table "books"')
            ->andFailOnExecute(new PDOException('Invalid SQL'));

        try {
            $mock->exec('select table "books"');

            $this->fail('Exception was not thrown');
        } catch (PDOException $e) {
            static::assertSame('Invalid SQL', $e->getMessage());
            static::assertSame(0, $e->getCode());
            static::assertNull($e->errorInfo);
        }
    }
}
