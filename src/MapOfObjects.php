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
     *
     * @param string $method The method on each contained object to call.
     * @param array|null $args The arguments to pass to the method.
     * @return \Haldayne\Boost\Map
     * @api
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
