<?php

namespace Tests\Xalaida\PDOMock;

use PDO;
use Xalaida\PDOMock\PDOMock;

class ParamComparatorNaturalTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function itShouldHandleParamsUsingNaturalComparator()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = ? and "year" = ? and "price" = ? and "deleted" = ? and "author" = ?')
            ->with(['published', 2024, 24.99, false, null])
            ->toMatchParamsNaturally();

        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ? and "price" = ? and "deleted" = ? and "author" = ?');

        $statement->bindValue(1, 'published', PDO::PARAM_STR);
        $statement->bindValue(2, 2024, PDO::PARAM_INT);
        $statement->bindValue(3, 24.99, PDO::PARAM_STR);
        $statement->bindValue(4, false, PDO::PARAM_BOOL);
        $statement->bindValue(5, null, PDO::PARAM_NULL);

        $result = $statement->execute();

        static::assertTrue($result);
    }

    /**
     * @test
     * @return void
     */
    public function itShouldFailWhenParamsDoNotMatchUsingNaturalComparator()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = ? and "year" = ?')
            ->with(['published', 2024])
            ->toMatchParamsNaturally();

        $statement = $pdo->prepare('select * from "books" where "status" = ? and "year" = ?');

        $statement->bindValue(1, 'published', PDO::PARAM_STR);
        $statement->bindValue(2, 2024, PDO::PARAM_STR);

        $this->expectException(static::getExpectationFailedExceptionClass());
        $this->expectExceptionMessage('Params do not match.');

        $statement->execute();
    }
}
