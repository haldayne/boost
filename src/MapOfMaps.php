<?php
namespace Haldayne\Boost;

/**
 * Implements a Map of Maps, which is a Map that must contain only Maps.
 */
class MapOfMaps extends GuardedMapAbstract
{
    /**
     * {@inheritDoc}
     */
    protected function allowed($value) {
        return $value instanceof Map;
    }
}
