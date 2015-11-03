<?php
namespace Haldayne\Boost;

/**
 * Implements a map of integers, that is a Map whose values must all pass the
 * `is_int` test.
 */
class MapOfInts extends MapOfNumerics
{
    /**
     * {@inheritDoc}
     */
    protected function allowed($value)
    {
        return is_int($value);
    }
}
