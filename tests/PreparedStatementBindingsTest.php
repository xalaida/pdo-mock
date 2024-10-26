<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Xala\Elomock\PDOMock;

class PreparedStatementBindingsTest extends TestCase
{
    #[Test]
    public function itShouldHandleQueryBindings(): void
    {
        $pdo = new PDOMock();

        $pdo->expectQuery('select * from "books" where "status" = ? and "year" = ? and "published" = ?')
            ->toBePrepared()
            ->withBinding(1, 'active', $pdo::PARAM_STR)
            ->withBinding(2, 2024, $pdo::PARAM_INT)
            ->withBinding(3, true, $pdo::PARAM_BOOL);

        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ? and "published" = ?');

        $statement->bindValue(1, 'active', $pdo::PARAM_STR);
        $statement->bindValue(2, 2024, $pdo::PARAM_INT);
        $statement->bindValue(3, true, $pdo::PARAM_BOOL);

        $result = $statement->execute();

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldHandleBindingsAsOptional(): void
    {
        $pdo = new PDOMock();

        $pdo->expectQuery('select * from "books" where "status" = ? and "year" = ? and "published" = ?')
            ->toBePrepared();

        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ? and "published" = ?');

        $statement->bindValue(1, 'active', $pdo::PARAM_STR);
        $statement->bindValue(2, 2024, $pdo::PARAM_INT);
        $statement->bindValue(3, true, $pdo::PARAM_BOOL);

        $result = $statement->execute();

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldHandleQueryBindingsUsingBindParam(): void
    {
        $pdo = new PDOMock();

        $pdo->expectQuery('select * from "books" where "status" = ? and "year" = ?')
            ->toBePrepared()
            ->withBinding(1, 'published', $pdo::PARAM_STR)
            ->withBinding(2, 2024, $pdo::PARAM_INT);

        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ?');

        $status = 'published';
        $year = 2024;

        $statement->bindParam(1, $status, $pdo::PARAM_STR);
        $statement->bindParam(2, $year, $pdo::PARAM_INT);

        $result = $statement->execute();

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldFailWhenQueryBindingsDontMatch(): void
    {
        $pdo = new PDOMock();

        $pdo->expectQuery('select * from "books" where "status" = ? and "year" = ? and "published" = ?')
            ->toBePrepared()
            ->withBinding(0, 'active', $pdo::PARAM_STR)
            ->withBinding(1, 2024, $pdo::PARAM_INT)
            ->withBinding(2, true, $pdo::PARAM_BOOL);

        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ? and "published" = ?');

        $statement->bindValue(1, 'active', $pdo::PARAM_STR);
        $statement->bindValue(2, 2024, $pdo::PARAM_INT);
        $statement->bindValue(3, true, $pdo::PARAM_BOOL);

        $this->expectException(ExpectationFailedException::class);

        $statement->execute();
    }

    #[Test]
    public function itShouldHandleQueryBindingsUsingAssociativeArray(): void
    {
        $pdo = new PDOMock();

        $pdo->expectQuery('select * from "books" where "status" = ? and "year" = ? and "published" = ?')
            ->toBePrepared()
            ->withBindings(['active', 2024, true], true);

        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ? and "published" = ?');

        $statement->bindValue(1, 'active', $pdo::PARAM_STR);
        $statement->bindValue(2, 2024, $pdo::PARAM_INT);
        $statement->bindValue(3, true, $pdo::PARAM_BOOL);

        $result = $statement->execute();

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldFailWhenQueryBindingsUsingAssociativeArrayDontMatch(): void
    {
        $pdo = new PDOMock();

        $pdo->expectQuery('select * from "books" where "status" = ? and "year" = ? and "published" = ?')
            ->toBePrepared()
            ->withBindings([2024, 'active', true]);

        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ? and "published" = ?');

        $statement->bindValue(1, 'active', $pdo::PARAM_STR);
        $statement->bindValue(2, 2024, $pdo::PARAM_INT);
        $statement->bindValue(3, true, $pdo::PARAM_BOOL);

        $this->expectException(ExpectationFailedException::class);

        $statement->execute();
    }

    #[Test]
    public function itShouldHandleQueryNamedBindings(): void
    {
        $pdo = new PDOMock();

        $pdo->expectQuery('select * from "books" where "category_id" = :category_id and "published" = :published')
            ->toBePrepared()
            ->withBinding('category_id', 7, $pdo::PARAM_INT)
            ->withBinding('published', true, $pdo::PARAM_BOOL);

        $statement = $pdo->prepare('select * from "books" where "category_id" = :category_id and "published" = :published');

        $statement->bindValue('category_id', 7, $pdo::PARAM_INT);
        $statement->bindValue('published', true, $pdo::PARAM_BOOL);

        $result = $statement->execute();

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldHandleQueryNamedBindingsUsingSingleAssociativeArray(): void
    {
        $pdo = new PDOMock();

        $pdo->expectQuery('select * from "books" where "status" = :status and "year" = :year and "published" = :published')
            ->toBePrepared()
            ->withBindings([
                'status' => 'active',
                'year' => 2024,
                'published' => true,
            ], true);

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year and "published" = :published');

        $statement->bindValue('year', 2024, $pdo::PARAM_INT);
        $statement->bindValue('status', 'active', $pdo::PARAM_STR);
        $statement->bindValue('published', true, $pdo::PARAM_BOOL);

        $result = $statement->execute();

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldFailWhenQueryNamedBindingsUsingSingleAssociativeArrayDontMatch(): void
    {
        $pdo = new PDOMock();

        $pdo->expectQuery('select * from "books" where "status" = :status and "year" = :year and "published" = :published')
            ->toBePrepared()
            ->withBindings([
                'status' => 'active',
                'year' => 2023,
                'published' => false,
            ]);

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year and "published" = :published');

        $statement->bindValue('year', 2024, $pdo::PARAM_INT);
        $statement->bindValue('status', 'active', $pdo::PARAM_STR);
        $statement->bindValue('published', true, $pdo::PARAM_BOOL);

        $this->expectException(ExpectationFailedException::class);

        $statement->execute();
    }

    #[Test]
    public function itShouldHandleExecBindings(): void
    {
        $pdo = new PDOMock();

        $pdo->expectQuery('select * from "books" where "status" = :status and "year" = :year')
            ->toBePrepared()
            ->withBinding(1, 'published')
            ->withBinding(2, 2024);

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $result = $statement->execute(['published', 2024]);

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldHandleExecBindingsTypes(): void
    {
        $pdo = new PDOMock();

        $pdo->expectQuery('select * from "books" where "status" = :status and "year" = :year')
            ->toBePrepared()
            ->withBindings(['published', 2024]);

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $result = $statement->execute(['published', 2024]);

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldFailWhenParamsOverwriteBoundValues(): void
    {
        $pdo = new PDOMock();

        $pdo->expectQuery('select * from "books" where "status" = :status and "year" = :year')
            ->toBePrepared()
            ->withBindings(['published', 2024]);

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $statement->bindValue(1, 'draft', $pdo::PARAM_STR);
        $statement->bindValue(2, 2024, $pdo::PARAM_INT);

        $this->expectException(ExpectationFailedException::class);

        $statement->execute([]);
    }

    #[Test]
    public function itShouldVerifyBindingsUsingCallableSyntax(): void
    {
        $pdo = new PDOMock();

        $pdo->expectQuery('select * from "books" where "status" = :status and "year" = :year')
            ->withBindingsUsing(function (array $bindings) use ($pdo) {
                static::assertEquals('draft', $bindings[1]['value']);
                static::assertEquals($pdo::PARAM_STR, $bindings[1]['type']);
                static::assertEquals(2024, $bindings[2]['value']);
                static::assertEquals($pdo::PARAM_INT, $bindings[2]['type']);
            });

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $statement->bindValue(1, 'draft', $pdo::PARAM_STR);
        $statement->bindValue(2, 2024, $pdo::PARAM_INT);

        $result = $statement->execute();

        static::assertTrue($result);
        $pdo->assertExpectationsFulfilled();
    }

    #[Test]
    public function itShouldFailWhenBindingsCallbackReturnsFalse(): void
    {
        $pdo = new PDOMock();

        $pdo->expectQuery('select * from "books" where "status" = :status and "year" = :year')
            ->withBindingsUsing(function () {
               return false;
            });

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $statement->bindValue(1, 'draft', $pdo::PARAM_STR);
        $statement->bindValue(2, 2024, $pdo::PARAM_INT);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Bindings do not match');

        $statement->execute();
    }
}
