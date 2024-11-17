<?php

namespace Tests\Xalaida\PDOMock;

use RuntimeException;
use Xalaida\PDOMock\PDOMock;
use Xalaida\PDOMock\ResultSet;

class FetchTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function itShouldHandleFetch()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared()
            ->willFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $row = $statement->fetch();

        static::assertIsArrayType($row);
        static::assertEquals([0 => 1, 'id' => 1, 1 => 'Kaidash’s Family', 'title' => 'Kaidash’s Family'], $row);

        $row = $statement->fetch();

        static::assertIsArrayType($row);
        static::assertEquals([0 => 2, 'id' => 2, 1 => 'Shadows of the Forgotten Ancestors', 'title' => 'Shadows of the Forgotten Ancestors'], $row);

        static::assertFalse(
            $statement->fetch()
        );
    }

    /**
     * @test
     * @return void
     */
    public function itShouldThrowExceptionWhenFetchResultIsNotSet()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared();

        $statement = $pdo->prepare('select * from "books"');

        $statement->execute();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ResultSet was not set. Use "willFetch" method to specify fetch results.');

        $statement->fetch($pdo::FETCH_OBJ);
    }

    /**
     * @test
     * @return void
     */
    public function itShouldFetchNothing()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared()
            ->willFetchNothing();

        $statement = $pdo->prepare('select * from "books"');

        $statement->execute();

        $row = $statement->fetch($pdo::FETCH_OBJ);

        static::assertFalse($row);
    }

    /**
     * @test
     * @return void
     */
    public function itShouldHandleIteratorAsRows()
    {
        $createGenerator = function () {
            yield [1, 'Kaidash’s Family'];
            yield [2, 'Shadows of the Forgotten Ancestors'];
        };

        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared()
            ->willFetch(
                (new ResultSet())
                    ->setCols(['id', 'title'])
                    ->setRows($createGenerator())
            );

        $statement = $pdo->prepare('select * from "books"');

        $statement->execute();

        $row = $statement->fetch($pdo::FETCH_OBJ);

        static::assertEquals((object) ['id' => 1, 'title' => 'Kaidash’s Family'], $row);

        $row = $statement->fetch($pdo::FETCH_OBJ);

        static::assertEquals((object) ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'], $row);

        $row = $statement->fetch($pdo::FETCH_OBJ);

        static::assertFalse($row);
    }
}
