<?php

namespace Tests\Xala\Elomock;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use PHPUnit\Framework\Attributes\Test;

class QueryExceptionTest extends TestCase
{
    #[Test]
    public function itShouldHandleQueryExceptions(): void
    {
        $connection = $this->getFakeConnection();

        $connection->expectQuery('insert into "users" ("name") values (?)')
            ->withBindings(['xala'])
            ->andThrow('Integrity constraint violation');

        $builder = (new Builder($connection))
            ->from('users');

        $this->expectException(QueryException::class);

        $builder->insert([
            ['name' => 'xala'],
        ]);
    }
}
