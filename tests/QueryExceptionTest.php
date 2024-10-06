<?php

namespace Tests\Xala\EloquentMock;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use PHPUnit\Framework\Attributes\Test;

class QueryExceptionTest extends TestCase
{
    #[Test]
    public function itShouldHandleQueryExceptions(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('insert into "users" ("name") values (?)')
            ->withBindings(['xala'])
            ->andThrow('Integrity constraint violation');

        $builder = (new Builder($connection))
            ->from('users');

        $this->expectException(QueryException::class);

        $builder->insert([
            ['name' => 'xala'],
        ]);
    }

    #[Test]
    public function itShouldThrowUniqueConstraintViolationException(): void
    {
        $connection = $this->getFakeConnection();

        $connection->shouldQuery('insert into "users" ("name") values (?)')
            ->withBindings(['xala'])
            ->andThrowUniqueConstraint();

        $builder = (new Builder($connection))
            ->from('users');

        $this->expectException(UniqueConstraintViolationException::class);

        $builder->insert([
            ['name' => 'xala'],
        ]);
    }
}
