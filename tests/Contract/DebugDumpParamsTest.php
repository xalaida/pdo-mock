<?php

namespace Tests\Xalaida\PDOMock\Contract;

use PDO;
use Tests\Xalaida\PDOMock\TestCase;
use Xalaida\PDOMock\PDOMock;

class DebugDumpParamsTest extends TestCase
{
    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldDumpDebugParams($pdo)
    {
        $statement = $pdo->prepare('select * from "books" where "title" like :search and "id" <> :id');

        $id = null;
        $search = '%test%';

        $statement->bindParam('search', $search, PDO::PARAM_STR);
        $statement->bindValue(':id', 7, PDO::PARAM_INT);
        $statement->bindColumn(1, $id);

        ob_start();

        $statement->debugDumpParams();

        $output = ob_get_contents();

        ob_end_clean();

        $debug = 'SQL: [64] select * from "books" where "title" like :search and "id" <> :id' . PHP_EOL .
            'Params:  2' . PHP_EOL .
            'Key: Name: [7] :search' . PHP_EOL .
            'paramno=-1' . PHP_EOL .
            'name=[7] ":search"' . PHP_EOL .
            'is_param=1' . PHP_EOL .
            'param_type=2' . PHP_EOL .
            'Key: Name: [3] :id' . PHP_EOL .
            'paramno=-1' . PHP_EOL .
            'name=[3] ":id"' . PHP_EOL .
            'is_param=1' . PHP_EOL .
            'param_type=1' . PHP_EOL;

        static::assertSame($debug, $output);
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldDumpDebugUnnamedParams($pdo)
    {
        $statement = $pdo->prepare('select * from "books" where "title" like ? and "id" <> ?');

        $id = null;
        $search = '%test%';

        $statement->bindParam(1, $search, PDO::PARAM_STR);
        $statement->bindValue(2, 7, PDO::PARAM_INT);
        $statement->bindColumn(1, $id);

        ob_start();

        $statement->debugDumpParams();

        $output = ob_get_contents();

        ob_end_clean();

        $debug = 'SQL: [56] select * from "books" where "title" like ? and "id" <> ?' . PHP_EOL .
            'Params:  2' . PHP_EOL .
            'Key: Position #0:' . PHP_EOL .
            'paramno=0' . PHP_EOL .
            'name=[0] ""' . PHP_EOL .
            'is_param=1' . PHP_EOL .
            'param_type=2' . PHP_EOL .
            'Key: Position #1:' . PHP_EOL .
            'paramno=1' . PHP_EOL .
            'name=[0] ""' . PHP_EOL .
            'is_param=1' . PHP_EOL .
            'param_type=1' . PHP_EOL;

        static::assertSame($debug, $output);
    }

    /**
     * @test
     * @dataProvider contracts
     * @param PDO $pdo
     * @return void
     */
    public function itShouldDumpDebugEmptyParams($pdo)
    {
        $statement = $pdo->prepare('select * from "books" where "title" like :search and "id" <> :id');

        ob_start();

        $statement->debugDumpParams();

        $output = ob_get_contents();

        ob_end_clean();

        $debug = 'SQL: [64] select * from "books" where "title" like :search and "id" <> :id' . PHP_EOL .
            'Params:  0' . PHP_EOL;

        static::assertSame($debug, $output);
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

        $pdo->expect('select * from "books" where "title" like :search and "id" <> :id')
            ->willFetchRows([
                ['id' => 1, 'title' => 'Kaidash’s Family'],
                ['id' => 2, 'title' => 'Shadows of the Forgotten Ancestors'],
            ]);

        return $pdo;
    }
}
