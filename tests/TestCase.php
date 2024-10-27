<?php

namespace Tests\Xala\Elomock;

use PDO;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function sqlite(): PDO
    {
        $sqlite = new PDO('sqlite::memory:');

        $sqlite->exec('create table "books" ("id" integer primary key autoincrement not null, "title" varchar not null)');

        return $sqlite;
    }

    protected function expectTriggerWarning(callable $callback, string | null $message = null)
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

        static::assertTrue($warningTriggered,'Warning was not triggered');

        return $result;
    }
}
