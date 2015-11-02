<?php
namespace Haldayne\Boost;

/**
 * Implements a map of numbers, that is values which pass the `is_numeric`
 * test.
 */
class MapOfNumerics extends Map
{
    /**
     * Create a new map of numerics.
     */
    public function __construct($collection = null)
    {
        parent::__construct(
            $collection,
            function ($value) { return is_numeric($value); }
        );
    }
}
