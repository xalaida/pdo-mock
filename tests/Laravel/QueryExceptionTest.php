<?php

namespace Tests\Xala\Elomock\Laravel;

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
            ->andFail('Integrity constraint violation');

        $builder = $connection->table('users');

        $this->expectException(QueryException::class);

        $builder->insert([
            ['name' => 'xala'],
        ]);
    }
}