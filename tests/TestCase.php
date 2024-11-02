<?php

namespace Tests\Xalaida\PDOMock;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Xalaida\PDOMock\ExpectationManager;
use Xalaida\PDOMock\Adapter\PHPUnit\AssertionValidator;

class TestCase extends BaseTestCase
{
    public static function setUpBeforeClass()
    {
        ExpectationManager::useAssertionValidator(new AssertionValidator());
    }

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

    protected static function assertIsArrayType($actual, $message = '')
    {
        static::assertTrue(is_array($actual), $message ?: gettype($actual) . ' is of type array');
    }

    protected static function assertIsObjectType($actual, $message = '')
    {
        static::assertTrue(is_object($actual), $message ?: gettype($actual) . ' is of type object');
    }
}
