<?php

namespace Xala\Elomock\Laravel;

use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Database\Query\Grammars\SQLiteGrammar;
use Illuminate\Database\Query\Grammars\SqlServerGrammar;
use Illuminate\Support\Facades\DB as Facade;

class DB extends Facade
{
    public static function fake(?string $connection = null): FakeConnection
    {
        $connection = $connection ?? static::$app['config']['database.default'];

        $config = static::$app['config']["database.connections.{$connection}"];

        $instance = new FakeConnection($config['database'], $config['prefix'], $config);

        $queryGrammar = match ($config['driver']) {
            'mysql' => new MySqlGrammar(),
            'pgsql' => new PostgresGrammar(),
            'sqlite' => new SQLiteGrammar(),
            'sqlsrv' => new SqlServerGrammar(),
            default => new Grammar(),
        };

        $instance->setQueryGrammar($queryGrammar);

        static::$app->make('db')->purge($connection);

        static::$app->make('db')->extend($connection, function () use ($instance) {
            return $instance;
        });

        return $instance;
    }
}
