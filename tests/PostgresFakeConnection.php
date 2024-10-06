<?php

namespace Tests\Xala\EloquentMock;

use Illuminate\Database\Query\Grammars\PostgresGrammar;
use PHPUnit\Framework\Attributes\Test;
use Xala\EloquentMock\FakeConnection;
use Xala\EloquentMock\FakeLastInsertIdGenerator;
use Xala\EloquentMock\FakePdo;

class PostgresFakeConnection extends TestCase
{
    #[Test]
    public function itShouldExtendPostgresConnection(): void
    {
        $pdo = new FakePdo(new FakeLastInsertIdGenerator());

        $connection = new FakeConnection($pdo);

        // TODO: use proper grammar
        $connection->setQueryGrammar(new PostgresGrammar());

        // TODO: fake Query Processor for
    }
}
