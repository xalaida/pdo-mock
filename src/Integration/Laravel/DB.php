<?php

namespace Xalaida\PDOMock\Integration\Laravel;

use Illuminate\Container\Container;
use Illuminate\Database\Connectors\Connector;
use Illuminate\Database\Connectors\ConnectorInterface;
use Xalaida\PDOMock\ParamComparatorNatural;
use Xalaida\PDOMock\PDOMock;

class DB
{
    /**
     * @param string|null $connection
     * @return PDOMock
     */
    public static function fake($connection = null)
    {
        self::configureMock();

        $container = static::getContainer();

        $config = $container->get('config');

        $connection = $connection ?: $config->get('database.default');

        $driver = $config->get("database.connections.{$connection}.driver");

        $pdo = new PDOMock();

        $connector = new class ($pdo) extends Connector implements ConnectorInterface
        {
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

    protected static function configureMock()
    {
        PDOMock::useParamComparator(new ParamComparatorNatural());
    }

    protected static function getContainer()
    {
        return Container::getInstance();
    }
}
