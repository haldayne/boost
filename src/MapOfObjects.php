<?php
namespace Haldayne\Boost;

/**
 * Implements a map that may only contain objects only.
 */
class MapOfObjects extends GuardedMapAbstract
{
    /**
     * Call the given method on every object in the map, and return the
     * results as a new map.
     */
    public function apply($method, array $args = [])
    {
        $result = new Map;
        $this->walk(function ($object, $key) use ($method, $args, $result) {
            $result[$key] = call_user_func_array([$object, $method], $args);
        });
        return $result;
    }

    // PROTECTED API

    /**
     * {@inheritDoc}
     */
    protected function allowed($value) {
        return is_object($value);
    }
}
