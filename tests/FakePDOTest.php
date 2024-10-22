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
    public function itShouldHandleQueryBindings(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "books" where "status" = ? and "year" = ? and "published" = ?')
            ->toBePrepared()
            ->withBinding(1, 'active', PDO::PARAM_STR)
            ->withBinding(2, 2024, PDO::PARAM_INT)
            ->withBinding(3, true, PDO::PARAM_BOOL);

        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ? and "published" = ?');

        $statement->bindValue(1, 'active', PDO::PARAM_STR);
        $statement->bindValue(2, 2024, PDO::PARAM_INT);
        $statement->bindValue(3, true, PDO::PARAM_BOOL);

        $result = $statement->execute();

        static::assertEquals(1, $result);
    }

    #[Test]
    public function itShouldHandleQueryBindingsUsingBindParam(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "books" where "status" = ? and "year" = ?')
            ->toBePrepared()
            ->withBinding(1, 'published', PDO::PARAM_STR)
            ->withBinding(2, 2024, PDO::PARAM_INT);

        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ?');

        $status = 'published';
        $year = 2024;

        $statement->bindParam(1, $status, PDO::PARAM_STR);
        $statement->bindParam(2, $year, PDO::PARAM_INT);

        $result = $statement->execute();

        static::assertEquals(1, $result);
    }

    #[Test]
    public function itShouldFailWhenQueryBindingsDontMatch(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "books" where "status" = ? and "year" = ? and "published" = ?')
            ->toBePrepared()
            ->withBinding(0, 'active', PDO::PARAM_STR)
            ->withBinding(1, 2024, PDO::PARAM_INT)
            ->withBinding(2, true, PDO::PARAM_BOOL);

        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ? and "published" = ?');

        $statement->bindValue(1, 'active', PDO::PARAM_STR);
        $statement->bindValue(2, 2024, PDO::PARAM_INT);
        $statement->bindValue(3, true, PDO::PARAM_BOOL);

        $this->expectException(ExpectationFailedException::class);

        $statement->execute();
    }

    #[Test]
    public function itShouldHandleQueryBindingsUsingAssociativeArray(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "books" where "status" = ? and "year" = ? and "published" = ?')
            ->toBePrepared()
            ->withBindings(['active', 2024, true], true);

        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ? and "published" = ?');

        $statement->bindValue(1, 'active', PDO::PARAM_STR);
        $statement->bindValue(2, 2024, PDO::PARAM_INT);
        $statement->bindValue(3, true, PDO::PARAM_BOOL);

        $result = $statement->execute();

        static::assertEquals(1, $result);
    }

    #[Test]
    public function itShouldFailWhenQueryBindingsUsingAssociativeArrayDontMatch(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "books" where "status" = ? and "year" = ? and "published" = ?')
            ->toBePrepared()
            ->withBindings([2024, 'active', true]);

        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ? and "published" = ?');

        $statement->bindValue(1, 'active', PDO::PARAM_STR);
        $statement->bindValue(2, 2024, PDO::PARAM_INT);
        $statement->bindValue(3, true, PDO::PARAM_BOOL);

        $this->expectException(ExpectationFailedException::class);

        $statement->execute();
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
            ], true);

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year and "published" = :published');

        $statement->bindValue('year', 2024, PDO::PARAM_INT);
        $statement->bindValue('status', 'active', PDO::PARAM_STR);
        $statement->bindValue('published', true, PDO::PARAM_BOOL);

        $result = $statement->execute();

        static::assertEquals(1, $result);
    }

    #[Test]
    public function itShouldFailWhenQueryNamedBindingsUsingSingleAssociativeArrayDontMatch(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "books" where "status" = :status and "year" = :year and "published" = :published')
            ->toBePrepared()
            ->withBindings([
                'status' => 'active',
                'year' => 2023,
                'published' => false,
            ]);

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year and "published" = :published');

        $statement->bindValue('year', 2024, PDO::PARAM_INT);
        $statement->bindValue('status', 'active', PDO::PARAM_STR);
        $statement->bindValue('published', true, PDO::PARAM_BOOL);

        $this->expectException(ExpectationFailedException::class);

        $statement->execute();
    }

    #[Test]
    public function itShouldHandleExecBindings(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "books" where "status" = :status and "year" = :year')
            ->toBePrepared()
            ->withBinding(1, 'published')
            ->withBinding(2, 2024);

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $result = $statement->execute(['published', 2024]);

        $this->assertEquals(1, $result);
    }

    #[Test]
    public function itShouldHandleExecBindingsTypes(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "books" where "status" = :status and "year" = :year')
            ->toBePrepared()
            ->withBindings(['published', 2024]);

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $result = $statement->execute(['published', 2024]);

        $this->assertEquals(1, $result);
    }

    #[Test]
    public function itShouldFailWhenParamsOverwriteBoundValues(): void
    {
        $pdo = new FakePDO();

        $pdo->expectQuery('select * from "books" where "status" = :status and "year" = :year')
            ->toBePrepared()
            ->withBindings(['published', 2024]);

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $statement->bindValue(1, 'draft', PDO::PARAM_STR);
        $statement->bindValue(2, 2024, PDO::PARAM_INT);

        $this->expectException(ExpectationFailedException::class);

        $statement->execute([]);
    }
}
