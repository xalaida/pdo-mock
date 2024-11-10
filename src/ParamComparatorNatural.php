<?php

namespace Xalaida\PDOMock;

use PDO;

class ParamComparatorNatural implements ParamComparatorInterface
{
    const TYPE_NULL = 'null';

    const TYPE_BOOL = 'boolean';

    const TYPE_INT = 'integer';

    /**
     * @var array<string, bool>
     */
    public static $defaultInferTypes = [
        self::TYPE_NULL => true,
        self::TYPE_BOOL => true,
        self::TYPE_INT => true
    ];

    /**
     * @var array<string, bool>
     */
    public $inferTypes;

    public function __construct()
    {
        $this->inferTypes = static::$defaultInferTypes;
    }

    /**
     * @param array<string, bool> $inferTypes
     * @return void
     */
    public static function inferTypes($inferTypes)
    {
        static::$defaultInferTypes = $inferTypes;
    }

    /**
     * @inheritDoc
     */
    public function compare($expectation, $reality)
    {
        if (count($expectation) !== count($reality)) {
            return false;
        }

        foreach ($expectation as $key => $expectedParam) {
            if (! array_key_exists($key, $reality)) {
                return false;
            }

            $actualParam = $reality[$key];

            if ($expectedParam['value'] !== $actualParam['value']) {
                return false;
            }

            if ($this->inferTypeByValue($expectedParam['value']) !== $actualParam['type']) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param mixed $value
     * @return int
     */
    public function inferTypeByValue($value)
    {
        $type = strtolower(gettype($value));

        $shouldInferType = isset($this->inferTypes[$type]) && $this->inferTypes[$type] === true;

        if (! $shouldInferType) {
            return PDO::PARAM_STR;
        }

        switch ($type) {
            case self::TYPE_NULL:
                return PDO::PARAM_NULL;

            case self::TYPE_BOOL:
                return PDO::PARAM_BOOL;

            case self::TYPE_INT:
                return PDO::PARAM_INT;

            default:
                return PDO::PARAM_STR;
        }
    }
}
