<?php
namespace Haldayne\Boost;

/**
 * Implements a Map of Maps.
 */
class MapOfMaps extends Map
{
    /**
     * Create a new map of maps.
     */
    public function __construct($collection = null)
    {
        parent::__construct(
            $collection,
            function ($value) {
                return $value instanceof Map;
            }
        );
    }
}
