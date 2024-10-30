<?php

namespace Tests\Xalaida\PDOMock;

use RuntimeException;
use Xalaida\PDOMock\PDOMock;
use Xalaida\PDOMock\ResultSet;

class FetchAllTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldHandleFetchAllInBothModeAsDefault()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared()
            ->andFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $rows = $statement->fetchAll();

        static::assertCount(2, $rows);
        static::assertIsArrayType($rows[0]);
        static::assertEquals([0 => 1, 'id' => 1, 1 => 'Kaidash’s Family', 'title' => 'Kaidash’s Family'], $rows[0]);
        static::assertIsArrayType($rows[1]);
        static::assertEquals([0 => 2, 'id' => 2, 1 => 'Shadows of the Forgotten Ancestors', 'title' => 'Shadows of the Forgotten Ancestors'], $rows[1]);
    }

    /**
     * @test
     */
    public function itShouldHandleFetchAllInAssocMode()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared()
            ->andFetchRows([
                ['id' => '1', 'title' => 'Kaidash’s Family'],
                ['id' => '2', 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $rows = $statement->fetchAll($pdo::FETCH_ASSOC);

        static::assertCount(2, $rows);
        static::assertIsArrayType($rows[0]);
        static::assertSame(['id' => '1', 'title' => 'Kaidash’s Family'], $rows[0]);
        static::assertIsArrayType($rows[1]);
        static::assertSame(['id' => '2', 'title' => 'Shadows of the Forgotten Ancestors'], $rows[1]);
    }

    /**
     * @test
     */
    public function itShouldHandleFetchAllInObjMode()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared()
            ->andFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $rows = $statement->fetchAll($pdo::FETCH_OBJ);

        static::assertCount(2, $rows);
        static::assertIsObjectType($rows[0]);
        static::assertEquals((object) ['id' => 1, 'title' => 'Kaidash’s Family'], $rows[0]);
        static::assertIsObjectType($rows[1]);
        static::assertEquals((object) ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'], $rows[1]);
    }

    /**
     * @test
     */
    public function itShouldUseCustomDefaultFetchMode()
    {
        $pdo = new PDOMock();
        $pdo->setAttribute($pdo::ATTR_DEFAULT_FETCH_MODE, $pdo::FETCH_OBJ);

        $pdo->expect('select * from "books"')
            ->toBePrepared()
            ->andFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $rows = $statement->fetchAll();

        static::assertCount(2, $rows);
        static::assertIsObjectType($rows[0]);
        static::assertEquals((object) ['id' => 1, 'title' => 'Kaidash’s Family'], $rows[0]);
        static::assertIsObjectType($rows[1]);
        static::assertEquals((object) ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'], $rows[1]);
    }

    /**
     * @test
     */
    public function itShouldUseCustomDefaultFetchModeForStatement()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared()
            ->andFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        $statement = $pdo->prepare('select * from "books"');

        $statement->setFetchMode($pdo::FETCH_OBJ);

        $result = $statement->execute();

        static::assertTrue($result);

        $rows = $statement->fetchAll();

        static::assertCount(2, $rows);
        static::assertIsObjectType($rows[0]);
        static::assertEquals((object) ['id' => 1, 'title' => 'Kaidash’s Family'], $rows[0]);
        static::assertIsObjectType($rows[1]);
        static::assertEquals((object) ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'], $rows[1]);
    }

    /**
     * @test
     */
    public function itShouldHandleFetchOne()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared()
            ->andFetchRow([
                'id' => 1,
                'title' => 'Kaidash’s Family',
            ]);

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $rows = $statement->fetchAll($pdo::FETCH_ASSOC);

        static::assertCount(1, $rows);
        static::assertIsArrayType($rows[0]);
        static::assertSame(['id' => 1, 'title' => 'Kaidash’s Family'], $rows[0]);
    }

    /**
     * @test
     */
    public function itShouldHandleFetchUsingResultSetInstance()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared()
            ->andFetch(
                (new ResultSet())
                    ->setCols(['id', 'title'])
                    ->addRow([1, 'Kaidash’s Family'])
                    ->addRow([2, 'Shadows of the Forgotten Ancestors'])
            );

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $rows = $statement->fetchAll($pdo::FETCH_ASSOC);

        static::assertCount(2, $rows);
        static::assertSame(['id' => 1, 'title' => 'Kaidash’s Family'], $rows[0]);
        static::assertSame(['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'], $rows[1]);
    }

    /**
     * @test
     */
    public function itShouldFailWhenColumnsAreMissingForAssocMode()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared()
            ->andFetch(
                (new ResultSet())
                    ->addRow([1, 'Kaidash’s Family'])
                    ->addRow([2, 'Shadows of the Forgotten Ancestors'])
            );

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Specify columns to result set');

        $statement->fetchAll($pdo::FETCH_ASSOC);
    }
}
