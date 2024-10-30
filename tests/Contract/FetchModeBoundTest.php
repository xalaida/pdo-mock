<?php

namespace Tests\Xala\Elomock\Contract;

use PDO;
use Tests\Xala\Elomock\TestCase;
use Xala\Elomock\PDOMock;

class FetchModeBoundTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     */
    public function itShouldHandleFetchInBoundModeUsingColumns(PDO $pdo): void
    {
        $statement = $pdo->prepare('select "id", "title", "status", "deleted" from "books" where "deleted" = ?');

        $statement->setFetchMode($pdo::FETCH_BOUND);

        $statement->bindValue(1, 0, $pdo::PARAM_BOOL);

        $statement->bindColumn(1, $id, $pdo::PARAM_INT);
        $statement->bindColumn(2, $title, $pdo::PARAM_STR);
        $statement->bindColumn(3, $status, $pdo::PARAM_NULL);
        $statement->bindColumn(4, $deleted, $pdo::PARAM_BOOL);

        $result = $statement->execute();
        static::assertTrue($result);

        $row = $statement->fetch();
        static::assertTrue($row);
        static::assertSame(1, $id);
        static::assertSame('Kaidash’s Family', $title);
        static::assertNull($status);
        static::assertFalse($deleted);

        $row = $statement->fetch();
        static::assertTrue($row);
        static::assertSame(2, $id);
        static::assertSame('Shadows of the Forgotten Ancestors', $title);
        static::assertNull($status);
        static::assertFalse($deleted);

        $row = $statement->fetch();
        static::assertFalse($row);
    }

    /**
     * @test
     * @dataProvider contracts
     */
    public function itShouldHandleFetchInBoundModeUsingNamedColumns(PDO $pdo): void
    {
        $statement = $pdo->prepare('select "id", "title", "status", "deleted" from "books" where "deleted" = ?');

        $statement->setFetchMode($pdo::FETCH_BOUND);

        $statement->bindValue(1, 0, $pdo::PARAM_BOOL);

        $statement->bindColumn('id', $id, $pdo::PARAM_INT);
        $statement->bindColumn('title', $title, $pdo::PARAM_STR);
        $statement->bindColumn('status', $status, $pdo::PARAM_NULL);
        $statement->bindColumn('deleted', $deleted, $pdo::PARAM_BOOL);
        $statement->bindColumn('poster', $poster, $pdo::PARAM_STR);

        $result = $statement->execute();
        static::assertTrue($result);

        $row = $statement->fetch();
        static::assertTrue($row);
        static::assertSame(1, $id);
        static::assertSame('Kaidash’s Family', $title);
        static::assertNull($status);
        static::assertFalse($deleted);
        static::assertNull($poster);

        $row = $statement->fetch();
        static::assertTrue($row);
        static::assertSame(2, $id);
        static::assertSame('Shadows of the Forgotten Ancestors', $title);
        static::assertNull($status);
        static::assertFalse($deleted);
        static::assertNull($poster);

        $row = $statement->fetch();
        static::assertFalse($row);
    }

    public static function contracts(): array
    {
        return [
            'SQLite' => [
                static::configureSqlite(),
            ],

            'Mock' => [
                static::configureMock(),
            ],
        ];
    }

    protected static function configureSqlite(): PDO
    {
        $pdo = new PDO('sqlite::memory:');

        $pdo->exec('create table "books" (
            "id" integer primary key autoincrement not null, 
            "title" varchar not null, 
            "status" varchar default "published", 
            "deleted" integer default 0
        )');

        $pdo->exec(
            'insert into "books"
            ("title", "status", "deleted") values 
            ("Kaidash’s Family", "published", 0),
            ("Shadows of the Forgotten Ancestors", "draft", 0)'
        );

        return $pdo;
    }

    protected static function configureMock(): PDOMock
    {
        $pdo = new PDOMock();

        $pdo->expect('select "id", "title", "status", "deleted" from "books" where "deleted" = ?')
            ->withParam(1, 0, $pdo::PARAM_BOOL)
            ->andFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family', 'status' => 'published', 'deleted' => false],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors', 'status' => 'draft', 'deleted' => false],
            ]);

        return $pdo;
    }
}
