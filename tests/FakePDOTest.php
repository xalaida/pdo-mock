<?php

namespace Tests\Xala\Elomock;

use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use Xala\Elomock\PDO\FakePDO;

class FakePDOTest extends TestCase
{
    #[Test]
    public function itShouldHandleQuery(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "users"');

        $result = $pdo->exec('select * from "users"');

        $this->assertEquals(1, $result);
    }

    #[Test]
    public function itShouldFailWhenQueryDoesntMatch(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "users"');

        $this->expectException(ExpectationFailedException::class);

        $pdo->exec('select * from "books"');
    }

    #[Test]
    public function itShouldHandlePreparedQuery(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "users"')
            ->toBePrepared();

        $statement = $pdo->prepare('select * from "users"');

        $result = $statement->execute();

        $this->assertEquals(1, $result);
    }

    #[Test]
    public function itShouldFailWhenStatementWasntPrepared(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "users"')
            ->toBePrepared();

        $this->expectException(ExpectationFailedException::class);

        $pdo->exec('select * from "users"');
    }

    #[Test]
    public function itShouldHandleQueryNamedBindings(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "books" where "category_id" = :category_id and "published" = :published')
            ->toBePrepared()
            ->withBinding('category_id', 7, PDO::PARAM_INT)
            ->withBinding('published', true, PDO::PARAM_BOOL);

        $statement = $pdo->prepare('select * from "books" where "category_id" = :category_id and "published" = :published');

        $statement->bindValue('category_id', 7, PDO::PARAM_INT);
        $statement->bindValue('published', true, PDO::PARAM_BOOL);

        $result = $statement->execute();

        static::assertEquals(1, $result);
    }

    #[Test]
    public function itShouldHandleQueryNamedBindingsUsingSingleAssociativeArray(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "books" where "status" = :status and "year" = :year and "published" = :published')
            ->toBePrepared()
            ->withBindings([
                'status' => 'active',
                'year' => 2024,
                'published' => true,
            ]);

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year and "published" = :published');

        $statement->bindValue('year', 2024, PDO::PARAM_INT);
        $statement->bindValue('status', 'active', PDO::PARAM_STR);
        $statement->bindValue('published', true, PDO::PARAM_BOOL);

        $result = $statement->execute();

        static::assertEquals(1, $result);
    }
}
