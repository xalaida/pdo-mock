<?php

namespace Tests\Xalaida\PDOMock;

use PDO;
use Xalaida\PDOMock\PDOMock;

class ParamComparatorStrictTest extends TestCase
{
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
            ->with(['published', 2024], [PDO::PARAM_STR, PDO::PARAM_INT])
            ->toMatchParamsStrictly();

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $this->expectException(static::getExpectationFailedExceptionClass());
        $this->expectExceptionMessage('Params do not match.');

        $statement->execute(['published', 2024]);
    }

    /**
     * @test
     * @return void
     */
    public function itShouldFailWhenBindParamsDoNotMatchUsingStrictComparator()
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
