<?php

namespace Xalaida\PDOMock\Integration\Laravel;

use Illuminate\Database\Connectors\Connector;
use Illuminate\Database\Connectors\ConnectorInterface;
use Xalaida\PDOMock\PDOMock;

class DB
{
    public static function fake($connection = null)
    {
        $connection = $connection ?: app('config')->get('database.default');

        $driver = app('config')->get("database.connections.{$connection}.driver");

        $pdo = new PDOMock();

        $connector = new class($pdo) extends Connector implements ConnectorInterface
        {
            protected $pdo;

            public function __construct($pdo)
            {
                $this->pdo = $pdo;
            }

            public function connect(array $config)
            {
                $pdo = $this->pdo;

                foreach ($this->getOptions($config) as $key => $value) {
                    $pdo->setAttribute($key, $value);
                }

                return $pdo;
            }
        };

        app()->instance("db.connector.{$driver}", $connector);

        return $pdo;
    }
}
