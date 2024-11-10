<?php

namespace Xalaida\PDOMock;

use InvalidArgumentException;

class QueryComparatorRegex implements QueryComparatorInterface
{
    /**
     * @inheritDoc
     */
    public function compare($expectation, $reality)
    {
        $expectation = $this->normalizeQuery($expectation);
        $reality = $this->normalizeQuery($reality);

        if (empty($expectation)) {
            throw new InvalidArgumentException('Expected SQL cannot be empty.');
        }

        $pattern = $this->injectRegexComponents($expectation);

        return preg_match("/^" . $pattern . "$/u", $reality) === 1;
    }

    /**
     * @param string $query
     * @return string
     */
    protected function normalizeQuery($query)
    {
        return trim(preg_replace('/\s+/', ' ', $query));
    }

    /**
     * @param string $pattern
     * @return string
     */
    protected function injectRegexComponents($pattern)
    {
        $counter = 0;
        $regexes = [];

        $patternWithPlaceholders = preg_replace_callback('/\{\{\s*(.*?)\s*\}\}/', function ($matches) use (&$counter, &$regexes) {
            $regexes[] = $matches[1];

            return '__PATTERN__' . (++$counter) . '__';
        }, $pattern);

        $escapedPattern = preg_quote($patternWithPlaceholders, '/');

        foreach ($regexes as $index => $regex) {
            $escapedPattern = preg_replace('/__PATTERN__' . ($index + 1) . '__/', $regex, $escapedPattern);
        }

        return $escapedPattern;
    }
}
