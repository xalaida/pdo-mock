<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use Xala\Elomock\PDOMock;

class PrepareTest extends TestCase
{
    #[Test]
    public function itShouldHandlePreparedStatement(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared();

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldFailOnUnexpectedQuery(): void
    {
        $pdo = new PDOMock();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Unexpected query: select * from "books"');

        $pdo->prepare('select * from "books"');
    }

    #[Test]
    public function itShouldFailWhenStatementIsNotPrepared(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared();

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Statement is not prepared');

        $pdo->exec('select * from "books"');
    }

    #[Test]
    public function itShouldFailWhenStatementIsNotExecuted(): void
    {
        $this->markTestSkipped('To be implemented');

        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared()
            ->toBeExecuted(false);

        $pdo->prepare('select * from "books"');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Some expectations were not fulfilled');

        $pdo->assertExpectationsFulfilled();
    }

    #[Test]
    public function itShouldHandleQueryParamsUsingBindParam(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = ? and "year" = ?')
            ->toBePrepared()
            ->withParam(1, 'published', $pdo::PARAM_STR)
            ->withParam(2, 2024, $pdo::PARAM_INT);

        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ?');

        $status = 'published';
        $year = 2024;

        $statement->bindParam(1, $status, $pdo::PARAM_STR);
        $statement->bindParam(2, $year, $pdo::PARAM_INT);

        $result = $statement->execute();

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldFailWhenQueryParamsDontMatch(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = ? and "year" = ? and "published" = ?')
            ->toBePrepared()
            ->withParam(0, 'active', $pdo::PARAM_STR)
            ->withParam(1, 2024, $pdo::PARAM_INT)
            ->withParam(2, true, $pdo::PARAM_BOOL);

        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ? and "published" = ?');

        $statement->bindValue(1, 'active', $pdo::PARAM_STR);
        $statement->bindValue(2, 2024, $pdo::PARAM_INT);
        $statement->bindValue(3, true, $pdo::PARAM_BOOL);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Params do not match');

        $statement->execute();
    }

    #[Test]
    public function itShouldHandleQueryParamsUsingAssociativeArray(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = ? and "year" = ? and "published" = ?')
            ->toBePrepared()
            ->with(['active', 2024, true], true);

        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ? and "published" = ?');

        $statement->bindValue(1, 'active', $pdo::PARAM_STR);
        $statement->bindValue(2, 2024, $pdo::PARAM_INT);
        $statement->bindValue(3, true, $pdo::PARAM_BOOL);

        $result = $statement->execute();

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldFailWhenQueryParamsUsingAssociativeArrayDontMatch(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = ? and "year" = ? and "published" = ?')
            ->toBePrepared()
            ->with([2024, 'active', true]);

        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ? and "published" = ?');

        $statement->bindValue(1, 'active', $pdo::PARAM_STR);
        $statement->bindValue(2, 2024, $pdo::PARAM_INT);
        $statement->bindValue(3, true, $pdo::PARAM_BOOL);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Params do not match');

        $statement->execute();
    }

    #[Test]
    public function itShouldHandleQueryNamedParams(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "category_id" = :category_id and "published" = :published')
            ->toBePrepared()
            ->withParam('category_id', 7, $pdo::PARAM_INT)
            ->withParam('published', true, $pdo::PARAM_BOOL);

        $statement = $pdo->prepare('select * from "books" where "category_id" = :category_id and "published" = :published');

        $statement->bindValue('category_id', 7, $pdo::PARAM_INT);
        $statement->bindValue('published', true, $pdo::PARAM_BOOL);

        $result = $statement->execute();

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldHandleQueryNamedParamsUsingSingleAssociativeArray(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = :status and "year" = :year and "published" = :published')
            ->toBePrepared()
            ->with([
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
    public function itShouldFailWhenQueryNamedParamsUsingSingleAssociativeArrayDontMatch(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = :status and "year" = :year and "published" = :published')
            ->toBePrepared()
            ->with([
                'status' => 'active',
                'year' => 2023,
                'published' => false,
            ]);

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year and "published" = :published');

        $statement->bindValue('year', 2024, $pdo::PARAM_INT);
        $statement->bindValue('status', 'active', $pdo::PARAM_STR);
        $statement->bindValue('published', true, $pdo::PARAM_BOOL);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Params do not match');

        $statement->execute();
    }

    #[Test]
    public function itShouldHandleExecParams(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = :status and "year" = :year')
            ->toBePrepared()
            ->withParam(1, 'published')
            ->withParam(2, 2024);

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $result = $statement->execute(['published', 2024]);

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldHandleExecParamsTypes(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = :status and "year" = :year')
            ->toBePrepared()
            ->with(['published', 2024]);

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $result = $statement->execute(['published', 2024]);

        static::assertTrue($result);
    }

    #[Test]
    public function itShouldFailWhenParamsOverwriteBoundValues(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = :status and "year" = :year')
            ->toBePrepared()
            ->with(['published', 2024]);

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $statement->bindValue(1, 'draft', $pdo::PARAM_STR);
        $statement->bindValue(2, 2024, $pdo::PARAM_INT);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Params do not match');

        $statement->execute([]);
    }

    #[Test]
    public function itShouldVerifyParamsUsingCallableSyntax(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = :status and "year" = :year')
            ->with(function (array $params) use ($pdo) {
                static::assertSame('draft', $params[1]['value']);
                static::assertSame($pdo::PARAM_STR, $params[1]['type']);
                static::assertSame(2024, $params[2]['value']);
                static::assertSame($pdo::PARAM_INT, $params[2]['type']);
            });

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $statement->bindValue(1, 'draft', $pdo::PARAM_STR);
        $statement->bindValue(2, 2024, $pdo::PARAM_INT);

        $result = $statement->execute();

        static::assertTrue($result);
        $pdo->assertExpectationsFulfilled();
    }

    #[Test]
    public function itShouldFailWhenParamsCallbackReturnsFalse(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = :status and "year" = :year')
            ->with(function () {
                return false;
            });

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $statement->bindValue(1, 'draft', $pdo::PARAM_STR);
        $statement->bindValue(2, 2024, $pdo::PARAM_INT);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Params do not match');

        $statement->execute();
    }

    #[Test]
    public function itShouldUseStatementFromPreviousExpectation(): void
    {
        $pdo = new PDOMock();

        $insertBookExpectation = $pdo->expect('insert into "books" values ("id", "title") values (:id, :title)');

        $pdo->expect('update "books" set "status" = :status where "id" = :id')
            ->with(function (array $params) use ($insertBookExpectation) {
                static::assertSame($insertBookExpectation->statement->params['id']['value'], $params['id']['value']);
                static::assertSame('published', $params['status']['value']);
            });

        $statement = $pdo->prepare('insert into "books" values ("id", "title") values (:id, :title)');
        $statement->bindValue('id', $id = rand(1, 50), $pdo::PARAM_INT);
        $statement->bindValue('title', 'The Forest Song', $pdo::PARAM_STR);
        $statement->execute();

        $statement = $pdo->prepare('update "books" set "status" = :status where "id" = :id');
        $statement->bindValue('id', $id, $pdo::PARAM_INT);
        $statement->bindValue('status', 'published', $pdo::PARAM_STR);
        $statement->execute();

        $pdo->assertExpectationsFulfilled();
    }
}
