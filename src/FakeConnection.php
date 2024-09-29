<?php

namespace Xala\EloquentMock;

use Illuminate\Database\Connection;

class FakeConnection extends Connection
{
    use FakeQueries;
}
