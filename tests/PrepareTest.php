<?php

namespace Tests\Xala\Elomock;

use Xala\Elomock\PDOMock;

class PrepareTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldHandlePreparedStatement()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared();

        $statement = $pdo->prepare('select * from "books"');

        $result = $statement->execute();

        static::assertTrue($result);
    }

    /**
     * @test
     */
    public function itShouldFailOnUnexpectedQuery()
    {
        $pdo = new PDOMock();

        $this->expectExceptionMessage('Unexpected query: select * from "books"');

        $pdo->prepare('select * from "books"');
    }

    /**
     * @test
     */
    public function itShouldFailWhenStatementIsNotPrepared()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared();

        $this->expectExceptionMessage('Statement is not prepared');

        $pdo->exec('select * from "books"');
    }

    /**
     * @test
     */
    public function itShouldHandleQueryParamsUsingBindParam()
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

    /**
     * @test
     */
    public function itShouldFailWhenQueryParamsDontMatch()
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

        $this->expectExceptionMessage('Params do not match');

        $statement->execute();
    }

    /**
     * @test
     */
    public function itShouldHandleQueryParamsUsingAssociativeArray()
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

    /**
     * @test
     */
    public function itShouldFailWhenQueryParamsUsingAssociativeArrayDontMatch()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = ? and "year" = ? and "published" = ?')
            ->toBePrepared()
            ->with([2024, 'active', true]);

        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ? and "published" = ?');

        $statement->bindValue(1, 'active', $pdo::PARAM_STR);
        $statement->bindValue(2, 2024, $pdo::PARAM_INT);
        $statement->bindValue(3, true, $pdo::PARAM_BOOL);

        $this->expectExceptionMessage('Params do not match');

        $statement->execute();
    }

    /**
     * @test
     */
    public function itShouldHandleQueryNamedParams()
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

    /**
     * @test
     */
    public function itShouldHandleQueryNamedParamsUsingSingleAssociativeArray()
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

    /**
     * @test
     */
    public function itShouldFailWhenQueryNamedParamsUsingSingleAssociativeArrayDontMatch()
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

        $this->expectExceptionMessage('Params do not match');

        $statement->execute();
    }

    /**
     * @test
     */
    public function itShouldHandleExecParams()
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

    /**
     * @test
     */
    public function itShouldHandleExecParamsTypes()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = :status and "year" = :year')
            ->toBePrepared()
            ->with(['published', 2024]);

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $result = $statement->execute(['published', 2024]);

        static::assertTrue($result);
    }

    /**
     * @test
     */
    public function itShouldFailWhenParamsOverwriteBoundValues()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = :status and "year" = :year')
            ->toBePrepared()
            ->with(['published', 2024]);

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $statement->bindValue(1, 'draft', $pdo::PARAM_STR);
        $statement->bindValue(2, 2024, $pdo::PARAM_INT);

        $this->expectExceptionMessage('Params do not match');

        $statement->execute([]);
    }

    /**
     * @test
     */
    public function itShouldVerifyParamsUsingCallableSyntax()
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

    /**
     * @test
     */
    public function itShouldFailWhenParamsCallbackReturnsFalse()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = :status and "year" = :year')
            ->with(function () {
                return false;
            });

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $statement->bindValue(1, 'draft', $pdo::PARAM_STR);
        $statement->bindValue(2, 2024, $pdo::PARAM_INT);

        $this->expectExceptionMessage('Params do not match');

        $statement->execute();
    }

    /**
     * @test
     */
    public function itShouldUseStatementFromPreviousExpectation()
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
