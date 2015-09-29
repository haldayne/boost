<?php
namespace Haldayne\Boost;

/**
 * Implements a map of maps.
 */
class MapOfMaps extends Map
{
    /**
     * Create a new map of maps.
     */
    public function __construct($collection = null)
    {
        parent::__construct($collection, [ $this, 'guard' ]);
    }

    /**
     * Ensure the given thing is a Map.
     *
     * @param mixed $value
     * @return boolean
     */
    public function guard($value)
    {
        return $value instanceof Guard;
    }
}
