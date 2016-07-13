<?php
namespace Haldayne\Boost;

/**
 * Implements a map that may only contain collection-like values.
 *
 * A collection-like value may be any of:
 *   - array
 *   - object
 *   - \Traversable
 *   - \Haldayne\Boost\Map 
 *   - \Haldayne\Boost\Contract\Arrayble
 *   - \Haldayne\Boost\Contract\Jsonable
 */
class MapOfCollections extends GuardedMapAbstract
{
    /**
     * Create a new map by pulling the given key out of all contained maps.
     *
     * Thinking of a Map of Collections as a two-dimensional array, this
     * method slices a column out:
     *
     * ```
     * use Haldayne\Boost\MapOfCollections;
     * $map = new MapOfCollections;
     * $map->push([ 'id' => 5, 'name' => 'Ada', 'age' => 16 ]);
     * $map->push([ 'id' => 6, 'name' => 'Bee', 'age' => 12 ]);
     * $map->push([ 'id' => 7, 'name' => 'Cam', 'age' => 37 ]);
     * var_dump(
     *   $map->pluck('name')->toArray();       // [ 'Ada', 'Bee', 'Cam' ]
     *   $map->pluck('name', 'id')->toArray(); // [ 5=>'Ada', 6=>'Bee', 7=>'Cam' ]
     * );
     * ```
     *
     * @param $mixed $key_for_value The key holding the new value.
     * @param $mixed $key_for_key The key holding the new value's key.
     * @return \Haldayne\Boost\Map
     * @api
     */
    public function pluck($key_for_value, $key_for_key = null)
    {
        $map = new Map;
        $this->walk(function ($v) use ($key_for_value, $key_for_key, $map) {
            // get the key to use
            if (null === $key_for_key) {
                $key = null;
            } else if ($v->has($key_for_key)) {
                $key = $v->get($key_for_key);
            } else {
                throw new \OutOfBoundsException();
            }

            // add the value at that key, provided we have one
            if ($v->has($key_for_value)) {
                $map[$key] = $v->get($key_for_value);
            } else {
                throw new \OutOfBoundsException();
            }
        });
        return $map;
    }

    // PROTECTED API

    /**
     * {@inheritDoc}
     */
    protected function allowed($value) {
        return $this->is_collection_like($value);
    }

    /**
     * {@inheritDoc}
     */
    protected function &normalize(&$value) {
        $map = new Map($value);
        return $map;
    }
}
