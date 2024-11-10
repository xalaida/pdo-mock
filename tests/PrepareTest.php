<?php

namespace Tests\Xalaida\PDOMock;

use PDO;
use Xalaida\PDOMock\PDOMock;

class PrepareTest extends TestCase
{
    /**
     * @test
     * @return void
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
     * @return void
     */
    public function itShouldFailOnUnexpectedQuery()
    {
        $pdo = new PDOMock();

        $this->expectExceptionMessage('Unexpected query: select * from "books"');

        $pdo->prepare('select * from "books"');
    }

    /**
     * @test
     * @return void
     */
    public function itShouldFailWhenStatementIsNotPrepared()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books"')
            ->toBePrepared();

        $this->expectException(static::getExpectationFailedExceptionClass());
        $this->expectExceptionMessage('Statement is prepared.');

        $pdo->exec('select * from "books"');
    }

    /**
     * @test
     * @return void
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
     * @return void
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

        $this->expectException(static::getExpectationFailedExceptionClass());
        $this->expectExceptionMessage('Params do not match.');

        $statement->execute();
    }

    /**
     * @test
     * @return void
     */
    public function itShouldHandleQueryParamsUsingShortSyntax()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = ? and "year" = ? and "published" = ?')
            ->toBePrepared()
            ->with(
                ['active', 2024, true],
                [$pdo::PARAM_STR, $pdo::PARAM_INT, $pdo::PARAM_BOOL]
            );

        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ? and "published" = ?');

        $statement->bindValue(1, 'active', $pdo::PARAM_STR);
        $statement->bindValue(2, 2024, $pdo::PARAM_INT);
        $statement->bindValue(3, true, $pdo::PARAM_BOOL);

        $result = $statement->execute();

        static::assertTrue($result);
    }

    /**
     * @test
     * @return void
     */
    public function itShouldHandleParamsTypes()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = :status and "year" = :year')
            ->toBePrepared()
            ->withParams(['published', 2024])
            ->withTypes([PDO::PARAM_STR, PDO::PARAM_INT]);

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $statement->bindValue(1, 'published', $pdo::PARAM_STR);
        $statement->bindValue(2, 2024, $pdo::PARAM_INT);

        $result = $statement->execute();

        static::assertTrue($result);
    }

    /**
     * @test
     * @return void
     */
    public function itShouldHandleParamsTypesUsingShortSyntax()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = :status and "year" = :year')
            ->toBePrepared()
            ->with(['published', 2024], [PDO::PARAM_STR, PDO::PARAM_INT]);;

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $statement->bindValue(1, 'published', $pdo::PARAM_STR);
        $statement->bindValue(2, 2024, $pdo::PARAM_INT);

        $result = $statement->execute();

        static::assertTrue($result);
    }

    /**
     * @test
     * @return void
     */
    public function itShouldFailWhenQueryParamsUsingShortSyntaxDontMatch()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = ? and "year" = ? and "published" = ?')
            ->toBePrepared()
            ->with([2024, 'active', true]);

        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ? and "published" = ?');

        $statement->bindValue(1, 'active', $pdo::PARAM_STR);
        $statement->bindValue(2, 2024, $pdo::PARAM_INT);
        $statement->bindValue(3, true, $pdo::PARAM_BOOL);

        $this->expectException(static::getExpectationFailedExceptionClass());
        $this->expectExceptionMessage('Params do not match.');

        $statement->execute();
    }

    /**
     * @test
     * @return void
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
     * @return void
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
            ], [
                'status' => $pdo::PARAM_STR,
                'year' => $pdo::PARAM_INT,
                'published' => $pdo::PARAM_BOOL,
            ]);

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year and "published" = :published');

        $statement->bindValue('year', 2024, $pdo::PARAM_INT);
        $statement->bindValue('status', 'active', $pdo::PARAM_STR);
        $statement->bindValue('published', true, $pdo::PARAM_BOOL);

        $result = $statement->execute();

        static::assertTrue($result);
    }

    /**
     * @test
     * @return void
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

        $this->expectException(static::getExpectationFailedExceptionClass());
        $this->expectExceptionMessage('Params do not match.');

        $statement->execute();
    }

    /**
     * @test
     * @return void
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
     * @return void
     */
    public function itShouldHandleExecParamsUsingShortSyntax()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = :status and "year" = :year')
            ->toBePrepared()
            ->with(['published', 2024], [PDO::PARAM_STR, PDO::PARAM_STR]);

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $result = $statement->execute(['published', 2024]);

        static::assertTrue($result);
    }

    /**
     * @test
     * @return void
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

        $this->expectException(static::getExpectationFailedExceptionClass());
        $this->expectExceptionMessage('Params do not match.');

        $statement->execute([]);
    }

    /**
     * @test
     * @return void
     */
    public function itShouldVerifyParamsUsingCallableSyntax()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = :status and "year" = :year')
            ->with(function ($params, $types) use ($pdo) {
                static::assertSame('draft', $params[1]);
                static::assertSame($pdo::PARAM_STR, $types[1]);
                static::assertSame(2024, $params[2]);
                static::assertSame($pdo::PARAM_INT, $types[2]);
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
     * @return void
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

        $this->expectException(static::getExpectationFailedExceptionClass());
        $this->expectExceptionMessage('Params do not match.');

        $statement->execute();
    }

    /**
     * @test
     * @return void
     */
    public function itShouldUseStatementFromAnotherStatement()
    {
        $pdo = new PDOMock();

        $insertBookExpectation = $pdo->expect('insert into "books" values ("id", "title") values (:id, :title)');

        $pdo->expect('update "books" set "status" = :status where "id" = :id')
            ->with(function ($params) use ($insertBookExpectation) {
                static::assertSame($insertBookExpectation->statement->params['id'], $params['id']);
                static::assertSame('published', $params['status']);
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

    /**
     * @test
     * @return void
     */
    public function itShouldHandleParamsUsingStrictComparator()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = :status and "year" = :year')
            ->with(['published', 2024], [PDO::PARAM_STR, PDO::PARAM_INT])
            ->toMatchParamsStrictly();

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $statement->bindValue(1, 'published', PDO::PARAM_STR);
        $statement->bindValue(2, 2024, PDO::PARAM_INT);

        $result = $statement->execute();

        static::assertTrue($result);
    }

    /**
     * @test
     * @return void
     */
    public function itShouldFailWhenParamsDoNotMatchUsingStrictComparator()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = :status and "year" = :year')
            ->with(['published', '2024'], [PDO::PARAM_STR, PDO::PARAM_STR])
            ->toMatchParamsStrictly();

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $statement->bindValue(1, 'published', PDO::PARAM_STR);
        $statement->bindValue(2, 2024, PDO::PARAM_INT);

        $this->expectException(static::getExpectationFailedExceptionClass());
        $this->expectExceptionMessage('Params do not match.');

        $statement->execute();
    }
}
