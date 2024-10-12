<?php

namespace Xala\Elomock;

use Illuminate\Database\Connection;
use Override;

class FakeConnection extends Connection
{
    use FakeQueries;

    public InsertIdGenerator $insertIdGenerator;

    public function __construct()
    {
        parent::__construct(null);

        $this->insertIdGenerator = new InsertIdGenerator();
    }

    #[Override]
    protected function getDefaultPostProcessor(): FakeProcessor
    {
        return new FakeProcessor();
    }

    #[Override]
    public function reconnectIfMissingConnection()
    {
        //
    }
}
