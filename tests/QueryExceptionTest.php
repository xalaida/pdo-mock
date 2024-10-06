<?php

namespace Tests\Xala\EloquentMock;

use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\Test;
use Xala\EloquentMock\FakePdoException;

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

        $this->expectException(FakePdoException::class);
        $this->expectExceptionMessage('Integrity constraint violation');

        $builder->insert([
            ['name' => 'xala'],
        ]);
    }
}
