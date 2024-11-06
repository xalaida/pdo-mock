<?php

namespace Xalaida\PDOMock;

use InvalidArgumentException;

class QueryMatcherRegex implements QueryMatcherInterface
{
    /**
     * @param string $expectation
     * @param string $reality
     * @return bool
     */
    public function match($expectation, $reality)
    {
        $expectation = $this->normalizeQuery($expectation);
        $reality = $this->normalizeQuery($reality);

        if (empty($expectation)) {
            throw new InvalidArgumentException('Expected SQL cannot be empty.');
        }

        $pattern = '/' . preg_quote($expectation, '/') . '/';

        return (bool) preg_match($pattern, $reality);
    }

    /**
     * @param string $query
     * @return string
     */
    protected function normalizeQuery($query)
    {
        return trim(preg_replace('/\s+/', ' ', $query));
    }
}
