<?php

namespace Tests\Xalaida\PDOMock;

use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * @param callable $callback
     * @param string|null $message
     * @return mixed
     */
    protected function expectTriggerWarning($callback, $message = null)
    {
        $warningTriggered = false;

        set_error_handler(function ($errno, $errstr) use (&$warningTriggered, $message) {
            $warningTriggered = true;

            static::assertTrue(E_WARNING === $errno || E_USER_WARNING === $errno);

            if ($message !== null) {
                static::assertSame($message, $errstr);
            }
        });

        $result = $callback();

        restore_error_handler();

        static::assertTrue($warningTriggered, 'Warning was not triggered');

        return $result;
    }

    protected static function assertIsArray($actual, $message = '')
    {
        static::assertTrue(is_array($actual), $message ?: gettype($actual) . ' is of type array');
    }

    protected static function assertIsObject($actual, $message = '')
    {
        static::assertTrue(is_object($actual), $message ?: gettype($actual) . ' is of type object');
    }
}
