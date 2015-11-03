<?php
namespace Haldayne\Boost;

/**
 * Implements a map of numbers, that is a Map whose values must all pass the
 * `is_numeric` test.
 */
class MapOfNumerics extends GuardedMapAbstract
{
    /**
     * {@inheritDoc}
     */
    protected function allowed($value)
    {
        return is_numeric($value);
    }
}
