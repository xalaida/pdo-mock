# Laravel Example

### DB helper class

Add a simple `DB` helper that swaps `PDO` with `PDOMock` using the anonymous `Connector` class:

```php
<?php

namespace Tests;

use Illuminate\Container\Container;
use Illuminate\Database\Connectors\Connector;
use Illuminate\Database\Connectors\ConnectorInterface;
use Xalaida\PDOMock\ParamComparatorNatural;
use Xalaida\PDOMock\PDOMock;

class DB
{
    public static function fake($connection = null): PDOMock
    {
        PDOMock::useParamComparator(new ParamComparatorNatural());

        $container = Container::getInstance();

        $config = $container->get('config');

        $connection = $connection ?: $config->get('database.default');

        $driver = $config->get("database.connections.{$connection}.driver");

        $pdo = new PDOMock();

        $connector = new class ($pdo) extends Connector implements ConnectorInterface {
            protected $pdo;

            public function __construct($pdo)
            {
                $this->pdo = $pdo;
            }

            public function connect($config)
            {
                $pdo = $this->pdo;

                foreach ($this->getOptions($config) as $key => $value) {
                    $pdo->setAttribute($key, $value);
                }

                return $pdo;
            }
        };

        $container->instance("db.connector.{$driver}", $connector);

        return $pdo;
    }
}
```

## Use DB helper in tests

```php
<?php

namespace Tests;

use App\Models\Book;
use PHPUnit\Framework\Attributes\Test;

class BookTest extends TestCase
{
    #[Test]
    public function itShouldSelectBooks(): void
    {
        $pdo = DB::fake();

        $pdo->expect('select * from "books" where "id" = ? limit 1')
            ->with(7)
            ->willFetchRow([
                'id' => 7,
                'title' => 'The Forest Song',
            ])

        $book = Book::find(7);

        $this->assertEquals(7, $book->id);
        $this->assertEquals('The Forest Song', $book->title);
        $pdo->assertExpectationsFulfilled();
    }
}
```
