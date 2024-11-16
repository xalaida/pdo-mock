<?php

namespace Tests\Xalaida\PDOMock;

use RuntimeException;
use Xalaida\PDOMock\PDOMock;
use Xalaida\PDOMock\ResultSet;

class FetchAllTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function itShouldHandleFetchAll()
    {
        $pdo = new PDOMock();
        $pdo->setAttribute($pdo::ATTR_STRINGIFY_FETCHES, true);

        $pdo->expect('select * from "books"')
            ->toBePrepared()
            ->willFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $rows = $statement->fetchAll($pdo::FETCH_OBJ);

        static::assertCount(2, $rows);
        static::assertIsObjectType($rows[0]);
        static::assertSame('1', $rows[0]->id);
        static::assertSame('Kaidash’s Family', $rows[0]->title);
        static::assertIsObjectType($rows[1]);
        static::assertSame('2', $rows[1]->id);
        static::assertSame('Shadows of the Forgotten Ancestors', $rows[1]->title);
    }

    /**
     * @test
     * @return void
     */
    public function itShouldHandleFetchOne()
    {
        $pdo = new PDOMock();
        $pdo->setAttribute($pdo::ATTR_STRINGIFY_FETCHES, true);

        $pdo->expect('select * from "books"')
            ->toBePrepared()
            ->willFetchRow([
                'id' => 1,
                'title' => 'Kaidash’s Family',
            ]);

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $rows = $statement->fetchAll($pdo::FETCH_ASSOC);

        static::assertCount(1, $rows);
        static::assertIsArrayType($rows[0]);
        static::assertSame(['id' => '1', 'title' => 'Kaidash’s Family'], $rows[0]);
    }

    /**
     * @test
     * @return void
     */
    public function itShouldHandleFetchUsingResultSetInstance()
    {
        $pdo = new PDOMock();
        $pdo->setAttribute($pdo::ATTR_STRINGIFY_FETCHES, true);

        $pdo->expect('select * from "books"')
            ->toBePrepared()
            ->willFetch(
                (new ResultSet())
                    ->setCols(['id', 'title'])
                    ->setRows([
                        [1, 'Kaidash’s Family'],
                        [2, 'Shadows of the Forgotten Ancestors']
                    ])
            );

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $rows = $statement->fetchAll($pdo::FETCH_ASSOC);

        static::assertCount(2, $rows);
        static::assertSame(['id' => '1', 'title' => 'Kaidash’s Family'], $rows[0]);
        static::assertSame(['id' => '2', 'title' => 'Shadows of the Forgotten Ancestors'], $rows[1]);
    }

    /**
     * @test
     * @return void
     */
    public function itShouldFailWhenColumnsAreMissingForAssocMode()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared()
            ->willFetch(
                (new ResultSet())
                    ->setRows([
                        [1, 'Kaidash’s Family'],
                        [2, 'Shadows of the Forgotten Ancestors']
                    ])
            );

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ResultSet columns were not set.');

        $statement->fetchAll($pdo::FETCH_ASSOC);
    }
}
