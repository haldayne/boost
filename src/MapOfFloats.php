<?php
namespace Haldayne\Boost;

/**
 * Implements a map of floats, that is a Map whose values must all pass the
 * `is_float` test.
 */
class MapOfFloats extends MapOfNumerics
{
    /**
     * {@inheritDoc}
     */
    protected function allowed($value)
    {
        return is_float($value);
    }
}
