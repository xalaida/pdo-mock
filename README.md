# PDOMock

Mock PDO library for PHP unit testing, allowing query expectation, parameter binding, fetch mode simulation, to test database interactions without a live connection.

Be careful, the library does not verify if the SQL query is valid or not.

No dependencies, supports all PHP versions from 5.6 to 8.3.
 
Inspired by the GO library [SQL-mock](https://github.com/DATA-DOG/go-sqlmock).

## Installation

```bash
composer require xalaida/pdo-mock
```

## Integration with PHPUnit

If you are using PHPUnit, probably you want to integrate its assertion mechanism with the PDOMock library.

To do so, register the extension in your configuration file for PHPUnit:

```xml
<extensions>
    <bootstrap class="Xalaida\PDOMock\Adapter\PHPUnit\Extension"/>
</extensions>
```

Or manually configure it in your `TestCase` class:

```php
use PHPUnit\Framework\TestCase as BaseTestCase;
use Xalaida\PDOMock\Adapter\PHPUnit\PHPUnitAdapter;
use Xalaida\PDOMock\PDOMock;

class TestCase extends BaseTestCase
{
    public static function setUpBeforeClass()
    {
        PDOMock::useAdapter(new PHPUnitAdapter());
    }
}
```

## Demo

Service:

```php
use PDO;

class BookService
{
    private PDO $pdo;

    public function __construct(PDO $pdo) 
    {
        $this->pdo = $pdo;
    }

    public function findById(int $id): array
    {
        $statement = $this->pdo->prepare('select * from "books" where "id" = :id');
        
        $statement->bindValue('id', $id);

        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }
}
```

Test Case:

```php
use PHPUnit\Framework\Attributes\Test;

class BookServiceTest
{
    #[Test]
    public function itShouldFindBookById(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('select * from "books" where "id" = :id')
            ->with(['id' => 7])
            ->andFetch([
                ['id' => 7, 'title' => 'The Forest Song']
            ]);

        $bookService = new BookService($pdo);

        $book = $bookService->findById(7);

        static::assertEquals(7, $book->id);
        static::assertEquals('The Forest Song', $book->title);
    }
}
```
