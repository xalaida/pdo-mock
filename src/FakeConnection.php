<?php

namespace Xala\Elomock;

use Illuminate\Database\Connection;

class FakeConnection extends Connection
{
    use FakeQueries;
}
