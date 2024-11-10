<?php

namespace Tests\Xalaida\PDOMock;

use PDO;
use Xalaida\PDOMock\PDOMock;

class ParamComparatorLooseTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function itShouldHandleParamsUsingLooseComparator()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = :status and "year" = :year')
            ->with(['published', '2024'], [PDO::PARAM_STR, PDO::PARAM_STR])
            ->toMatchParamsLoosely();

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
    public function itShouldFailWhenParamsDoNotMatchUsingLooseComparator()
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "status" = :status and "year" = :year')
            ->with(['published', 2020])
            ->toMatchParamsLoosely();

        $statement = $pdo->prepare('select * from "books" where "status" = :status and "year" = :year');

        $statement->bindValue(1, 'published');
        $statement->bindValue(2, 2000);

        $this->expectException(static::getExpectationFailedExceptionClass());
        $this->expectExceptionMessage('Params do not match.');

        $statement->execute();
    }
}
