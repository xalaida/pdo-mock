<?php

namespace Tests\Xalaida\PDOMock;

use PDOException;
use Xalaida\PDOMock\PDOMockException;
use Xalaida\PDOMock\PDOMock;

class FailedQueryTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function itShouldFailWithQueryException()
    {
        $pdo = new PDOMock();
        $pdo->setAttribute($pdo::ATTR_ERRMODE, $pdo::ERRMODE_EXCEPTION);

        $pdo->expect('insert into "books" ("id", "title") values (1, null)')
            ->willFailOnExecute(PDOMockException::fromErrorInfo(
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

    /**
     * @test
     * @return void
     */
    public function itShouldFailUsingCustomErrorException()
    {
        $pdo = new PDOMock();
        $pdo->setAttribute($pdo::ATTR_ERRMODE, $pdo::ERRMODE_EXCEPTION);

        $pdo->expect('select table "books"')
            ->willFailOnExecute(new PDOException('Invalid SQL'));

        try {
            $pdo->exec('select table "books"');

            $this->fail('Exception was not thrown');
        } catch (PDOException $e) {
            static::assertSame('Invalid SQL', $e->getMessage());
            static::assertSame(0, $e->getCode());
            static::assertNull($e->errorInfo);
        }
    }
}
