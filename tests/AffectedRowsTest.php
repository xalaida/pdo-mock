<?php

namespace Tests\Xala\Elomock;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Xala\Elomock\PDOMock;

class AffectedRowsTest extends TestCase
{
    #[Test]
    public function itShouldReturnZeroAffectedRowsByDefault(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('insert into "users" ("name") values ("john")');

        $result = $pdo->exec('insert into "users" ("name") values ("john")');

        static::assertSame(0, $result);
    }

    #[Test]
    public function itShouldReturnSpecifiedAffectedRows(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('insert into "users" ("name") values ("john"), ("jane")')
            ->affecting(2);

        $result = $pdo->exec('insert into "users" ("name") values ("john"), ("jane")');

        static::assertSame(2, $result);
    }

    #[Test]
    public function itShouldReturnZeroAffectedRowsUsingNotExecutedPreparedStatement(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('delete from "users"')
            ->toBePrepared();

        $statement = $pdo->prepare('delete from "users"');

        static::assertSame(0, $statement->rowCount());
    }

    #[Test]
    public function itShouldIgnoreSpecifiedAffectedRowsUsingNotExecutedPreparedStatement(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('insert into "users" ("name") values ("john"), ("jane")')
            ->toBePrepared()
            ->affecting(2);

        $statement = $pdo->prepare('insert into "users" ("name") values ("john"), ("jane")');

        static::assertSame(0, $statement->rowCount());
    }

    #[Test]
    public function itShouldReturnAffectedRowsUsingPreparedStatement(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('insert into "users" ("name") values ("john"), ("jane")')
            ->toBePrepared()
            ->affecting(2);

        $statement = $pdo->prepare('insert into "users" ("name") values ("john"), ("jane")');

        $statement->execute();

        static::assertSame(2, $statement->rowCount());
    }
}
