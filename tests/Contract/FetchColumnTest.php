<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class FetchColumnTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldFetchColumn($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_STRINGIFY_FETCHES, true);

        $statement = $pdo->prepare('select * from "books"');

        $column = $statement->fetchColumn();

        static::assertFalse($column);

        $statement->execute();

        $column = $statement->fetchColumn();

        static::assertSame('1', $column);

        $column = $statement->fetchColumn(1);

        static::assertSame('Shadows of the Forgotten Ancestors', $column);

        $column = $statement->fetchColumn();

        static::assertFalse($column);
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldThrowExceptionWhenIndexIsInvalid($pdo)
    {
        if (PHP_VERSION_ID < 80000) {
            $this->markTestSkipped('Only for PHP >= 8.0.0');
        }

        $statement = $pdo->prepare('select * from "books"');

        $statement->execute();

        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('Invalid column index');

        $statement->fetchColumn(2);
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldThrowExceptionWhenStatementIsNotExecuted($pdo)
    {
        $pdo->setAttribute($pdo::ATTR_STRINGIFY_FETCHES, true);

        $statement = $pdo->prepare('select * from "books"');

        $value = $statement->fetchColumn();

        static::assertFalse($value);
    }

    /**
     * @return array<string, array<int, PDO>>
     */
    public static function contracts()
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

    /**
     * @return PDO
     */
    protected static function configureSqlite()
    {
        $pdo = new PDO('sqlite::memory:');

        $pdo->exec('create table "books" ("id" integer primary key autoincrement not null, "title" varchar not null)');

        $pdo->exec('insert into "books" ("title") values ("Kaidash’s Family"), ("Shadows of the Forgotten Ancestors")');

        return $pdo;
    }

    /**
     * @return PDOMock
     */
    protected static function configureMock()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->willFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        return $pdo;
    }
}
