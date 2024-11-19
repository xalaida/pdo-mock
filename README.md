# PDOMock

![Packagist](https://img.shields.io/packagist/v/xalaida/pdo-mock)
![Build](https://img.shields.io/github/actions/workflow/status/xalaida/pdo-mock/ci.yaml?branch=master)
![Coverage](https://img.shields.io/codecov/c/github/xalaida/pdo-mock)
![License](https://img.shields.io/github/license/xalaida/pdo-mock)

PDOMock is a PHP library for testing database interactions without relying on an actual database connection.

Unlike higher-level abstractions like the repository pattern, PDOMock validates database queries at the SQL level, providing greater control and insights into query execution.

This testing technique dramatically accelerates test suite performance and helps identify common issues such as N+1 queries.

Mainly inspired by the Go library [SQL-mock](https://github.com/DATA-DOG/go-sqlmock).

## 🔍 Overview

- Supports any SQL dialect
- Supports most PDO features
- Works with most popular ORMs
- PHPUnit integration
- Supports all PHP versions from 5.6
- No dependencies

## 🚀 Installation

Install the library using Composer:

```bash
composer require xalaida/pdo-mock
```

## 📽️ Example Usage

Here’s an example of how to use PDOMock in your test cases. 
Let's assume we have the following service that interacts with the database via PDO:

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
        $statement = $this->pdo->prepare('SELECT * FROM "books" WHERE "id" = :id');
        $statement->bindValue('id', $id);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }
}
```

Now, we can write a test for this service:

```php
use PHPUnit\Framework\Attributes\Test;

class BookServiceTest
{
    #[Test]
    public function itShouldFindBookById(): void
    {
        $pdo = new PDOMock();

        $pdo->expect('SELECT * FROM "books" WHERE "id" = :id')
            ->with(['id' => 7])
            ->willFetch([
                ['id' => 7, 'title' => 'The Forest Song']
            ]);

        $bookService = new BookService($pdo);

        $book = $bookService->findById(7);

        static::assertEquals(7, $book['id']);
        static::assertEquals('The Forest Song', $book['title']);
    }
}
```

## 🧾 Documentation

#### Query Expectation

PDOMock requires that you set up expectations for each query.
To define a simple query expectation, use the `expect` method:

```php
use Xalaida\PDOMock\PDOMock;

$pdo = new PDOMock();

$pdo->expect('SELECT * FROM "books"');
```

By default, queries are validated by an exact string match. 
However, if the query is written in a different format (e.g., multiline), the test will fail. 
To allow for more flexibility, you can use `toMatchRegex`:

```php
$pdo->expect('SELECT * FROM "books" LIMIT 1')
    ->toMatchRegex();
```

This will match the query regardless of whitespace formatting:

```php
$pdo->query('
    SELECT * FROM "books"
    LIMIT 1
');
```

And also allows to provide regex placeholders if you want to skip some parts of the query:

```php
$pdo->expect('INSERT INTO "books" ({{ .* }}) VALUES ({{ .* }})')
    ->toMatchRegex();
```

**Note:** Be cautious when using `toMatchRegex` as it will also strip whitespace from quoted values. 

For "write" queries, it's recommended to use a strict query comparator.

Keep in mind that the query comparator **does not use any SQL parsers**, so you should manually verify that all queries are valid SQL.

The expect method works with any PDO method, including exec, query, or prepared statements. 
To verify that a statement was prepared, you can specify this manually:

```php
$pdo->expect('SELECT * FROM "books" LIMIT 1')
    ->toBePrepared();
```

#### Parameters Validation

You can validate query parameters using the `with` method, which allows you to pass an array of parameter values and types:

```php
$pdo->expect('SELECT * FROM "books" WHERE "id" = :id')
    ->with(['id' => 7]);
```

For anonymous placeholders (?), use this syntax:

```php
$pdo->expect('SELECT * FROM "books" WHERE "year" = ? AND "status" = ?')
    ->with([2020, 'published']);
```

You can also specify the parameter types:

```php
$pdo->expect('SELECT * FROM "books" WHERE "id" = :id')
    ->with(['id' => 7], ['id' => PDO::PARAM_INT]);
```

By default, parameters are validated using a strict comparator that checks both values and types. 
For a looser comparison, you can use `toMatchParamsLoosely`:

```php
$pdo->expect('SELECT * FROM "books" WHERE "id" = :id')
    ->with(['id' => 7])
    ->toMatchParamsLoosely();
```

This will pass even if the value is cast to a different type (e.g., '7' instead of 7).

Alternatively, use `toMatchParamsNaturally` to allow automatic type matching:

```php
$pdo->expect('SELECT * FROM "books" WHERE "id" = :id')
    ->with(['id' => 7])
    ->toMatchParamsNaturally();
```

For manual validation, you can use a callback:

```php
$pdo->expect('SELECT * FROM "books" WHERE "id" = ?')
    ->with(function (array $params, array $types) {
        static::assertSame(7, $params[1]);
        static::assertSame(PDO::PARAM_INT, $types[1]);
    });
```

Validation fails only if the callback returns `false`. 
Additionally, the `$params` and `$types` arrays use a 1-based index, consistent with PDO bind functions.

#### Result Set Simulation

To simulate query results, use the `willFetch` method:

```php
$pdo->expect('SELECT * FROM "books" WHERE "id" = :id')
    ->with(['id' => 7])
    ->willFetch([
        ['id' => 7, 'title' => 'The Forest Song']
    ]);
```

For more complex result sets, you can use a `ResultSet` instance:

```php
use Xalaida\PDOMock\ResultSet;

$pdo->expect('SELECT * FROM "books" LIMIT 3')
    ->willFetch(
        (new ResultSet())
            ->setCols(['id', 'title'])
            ->setRows([
                [1, 'The Forest Song'],
                [2, 'Kaidash’s Family'],
                [3, 'Shadows of the Forgotten Ancestors'],
            ])
    );
```

#### Insert, Update, Delete Queries

For insert queries, specify the insert ID:

```php
$pdo->expect('INSERT INTO "books" ("title") VALUES (?)')
    ->with(['The Forest Song'])
    ->willInsertId(7);
```

After execution, the `$pdo->lastInsertId()` method will return 7.

For update and delete queries, you can specify the number of affected rows:

```php
$pdo->expect('UPDATE "books" SET "status" = :status WHERE "year" = :year')
    ->with(['year' => 2020, 'status' => 'draft'])
    ->willAffect(5);
```

#### Transaction Management

You can verify transaction behavior with the following methods: `expectBeginTransaction`, `expectCommit`, and `expectRollback`. 
Here's an example:

```php
$pdo->expectBeginTransaction();
$pdo->expect('INSERT INTO "books" ("title") VALUES ("Kaidash’s Family")');
$pdo->expectCommit();
```

#### Error Simulation

To simulate query exceptions, use the `willFail` method:

```php
$pdo->expect('INSERT INTO "books" ("id", "title") VALUES (1, null)')
    ->willFail(PDOMockException::fromErrorInfo(
        'SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: books.title',
        '23000',
        'NOT NULL constraint failed: books.title',
        19
    ));
```

#### Post-Execution Assertions

To verify that all expected queries were executed, use `assertExpectationFulfilled`:

```php
$pdo->expect('SELECT * FROM "books" WHERE "id" = :id');
$pdo->assertExpectationFulfilled();
```

#### Default Comparators

You can set default comparators that will apply to all future queries:

```php
use Xalaida\PDOMock\PDOMock;
use Xalaida\PDOMock\QueryComparatorRegex;
use Xalaida\PDOMock\ParamComparatorNatural;

PDOMock::useQueryComparator(new QueryComparatorRegex());
PDOMock::useParamComparator(new ParamComparatorNatural());
```

#### Integration with PHPUnit

If you're using PHPUnit, you may want to integrate its assertion mechanism with PDOMock. To do so, register the extension in your PHPUnit configuration file:

```xml
<extensions>
    <bootstrap class="Xalaida\PDOMock\Adapter\PHPUnit\PHPUnitExtension"/>
</extensions>
```

Alternatively, you can manually configure it in your `TestCase` class:

```php
use PHPUnit\Framework\TestCase as BaseTestCase;
use Xalaida\PDOMock\Adapter\PHPUnit\PHPUnitAdapter;
use Xalaida\PDOMock\PDOMock;

class TestCase extends BaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        PDOMock::useAdapter(new PHPUnitAdapter());
    }
}
```

## 📜 License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
