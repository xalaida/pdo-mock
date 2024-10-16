<?php

namespace Xala\Elomock;

use Closure;
use Exception;
use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Override;
use PHPUnit\Framework\TestCase;

class FakeConnection extends Connection
{
    use HandleTransactions;

    public array $expectations = [];

    public array $deferredQueries = [];

    public bool $deferWriteQueries = false;

    public int | string | null $lastInsertId = null;

    public InsertIdGenerator $insertIdGenerator;

    public function __construct()
    {
        parent::__construct(null);

        $this->insertIdGenerator = new InsertIdGenerator();

        $this->enableQueryLog();
    }

    public function deferWriteQueries(bool $deferWriteQueries = true): void
    {
        $this->deferWriteQueries = $deferWriteQueries;
    }

    public function getLastInsertId(): int | string | null
    {
        if (! is_null($this->lastInsertId)) {
            $lastInsertId = $this->lastInsertId;

            $this->lastInsertId = null;

            return $lastInsertId;
        }

        return $this->insertIdGenerator->generate();
    }

    public function expectQuery(string $query, array | Closure | null $bindings = null): Expectation
    {
        $expectation = new Expectation($query, $bindings);

        $this->expectations[] = $expectation;

        return $expectation;
    }

    #[Override]
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return [];
            }

            $expectation = $this->handleQueryExpectation($query, $bindings);

            return $expectation->rows;
        });
    }

    public function cursor($query, $bindings = [], $useReadPdo = true)
    {
        $rows = $this->select($query, $bindings, $useReadPdo);

        foreach ($rows as $row) {
            yield $row;
        }
    }

    #[Override]
    public function statement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return true;
            }

            if ($this->deferWriteQueries) {
                $this->deferredQueries[] = [
                    'query' => $query,
                    'bindings' => $bindings,
                ];

                return true;
            }

            $expectation = $this->handleQueryExpectation($query, $bindings);

            $this->lastInsertId = $expectation->lastInsertId;

            $this->recordsHaveBeenModified();

            return true;
        });
    }

    #[Override]
    public function affectingStatement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return 0;
            }

            if ($this->deferWriteQueries) {
                $this->deferredQueries[] = [
                    'query' => $query,
                    'bindings' => $bindings,
                ];

                return 1;
            }

            $expectation = $this->handleQueryExpectation($query, $bindings);

            $this->recordsHaveBeenModified(
                $expectation->rowCount > 0
            );

            return $expectation->rowCount;
        });
    }

    #[Override]
    public function unprepared($query)
    {
        return $this->run($query, [], function ($query) {
            if ($this->pretending()) {
                return true;
            }

            if ($this->deferWriteQueries) {
                $this->deferredQueries[] = [
                    'query' => $query,
                    'bindings' => [],
                ];

                return 1;
            }

            $expectation = $this->handleQueryExpectation($query, []);

            $this->recordsHaveBeenModified(
                $expectation->rowCount > 0
            );

            return $expectation->rowCount;
        });
    }

    #[Override]
    protected function run($query, $bindings, Closure $callback)
    {
        foreach ($this->beforeExecutingCallbacks as $beforeExecutingCallback) {
            $beforeExecutingCallback($query, $bindings, $this);
        }

        $result = $callback($query, $bindings);

        $this->logQuery($query, $bindings, 0);

        return $result;
    }

    // TODO: check if it works with latest laravel version
    protected function isUniqueConstraintError(Exception $exception): bool
    {
        return $exception->getMessage() === 'Unique constraint error';
    }

    public function assertExpectationsFulfilled(): void
    {
        TestCase::assertEmpty($this->expectations, 'Some expectations were not fulfilled.');
    }

    public function assertDeferredQueriesFulfilled(): void
    {
        $queriesFormatted = implode(PHP_EOL, array_map(function (array $query) {
            return sprintf('%s [%s]', $query['query'], implode(', ', $query['bindings']));
        }, $this->deferredQueries));

        TestCase::assertEmpty($this->deferredQueries, 'Some write queries were not fulfilled:' . PHP_EOL . $queriesFormatted);
    }

    public function assertQueried(string $query, array | Closure | null $bindings = null): void
    {
        TestCase::assertNotEmpty($this->deferredQueries, 'No queries were executed');

        $deferredQuery = array_shift($this->deferredQueries);

        TestCase::assertEquals($query, $deferredQuery['query'], 'Query does not match');

        $this->validateBindings($bindings, $deferredQuery['bindings'], $deferredQuery['query']);
    }

    protected function handleQueryExpectation(string $query, array $bindings): Expectation
    {
        $bindings = $this->prepareBindings($bindings);

        TestCase::assertNotEmpty($this->expectations, sprintf('Unexpected query: [%s] [%s]', $query, implode(', ', $bindings)));

        $expectation = array_shift($this->expectations);

        TestCase::assertEquals($expectation->query, $query, sprintf('Unexpected query: [%s] [%s]', $query, implode(', ', $bindings)));

        $this->validateBindings($expectation->bindings, $bindings, $query);

        if ($expectation->exception) {
            throw new QueryException(
                $this->getName(), $query, $bindings, $expectation->exception
            );
        }

        return $expectation;
    }

    protected function validateBindings(array | Closure | null $expectedBindings, array $bindings, string $query): void
    {
        if (is_null($expectedBindings)) {
            return;
        }

        if (is_array($expectedBindings)) {
            TestCase::assertEquals($expectedBindings, $bindings, sprintf('Unexpected query bindings: [%s] [%s]', $query, implode(', ', $bindings)));
        }

        if (is_callable($expectedBindings)) {
            TestCase::assertNotFalse(call_user_func($expectedBindings, $bindings), sprintf('Unexpected query bindings: [%s] [%s]', $query, implode(', ', $bindings)));
        }
    }

    #[Override]
    protected function getDefaultPostProcessor(): FakeProcessor
    {
        return new FakeProcessor();
    }
}
