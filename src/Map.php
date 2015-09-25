<?php
namespace Haldayne\Boost;

use Haldayne\Boost\Contract\Arrayable;
use Haldayne\Boost\Contract\Jsonable;

/**
 * An improvement on PHP associative arrays.
 *
 * Sports a fluent interface accepting callables to drive array operations,
 * similar in spirit to jQuery. Supports keys of any type: scalars, even
 * arrays and objects!  Used in place of `array ()` and `[]`, your code will
 * be easier to write *and* read.
 *
 * In the API, a formal variable named `$collection` must be one of:
 *   - Haldayne\Boost\Map
 *   - Haldayne\Boost\Contract\Arrayable
 *   - Haldayne\Boost\Contract\Jsonable
 *   - \Traversable
 *   - object
 *   - array
 *
 * In the API, a formal variable named `$code` must be either a callable or
 * a string representing actual PHP code. When giving strings, be mindful:
 * user-supplied string code is a security risk, and string code you right is
 * checked only at run-time. Also, be mindful that these strings can contain
 * `$v` and `$k`, which represent the value and key being passed in.  Finally,
 * code that "fails" means it returns a PHP empty value: `''`, `0`, `0.0`,
 * `'0'`, `null`, `false`, or `array ()`. If the code returns *any other value*
 * it "passes".
 *
 * In the API, a formal variable named `$key` may be of *any* type.
 */
class Map implements \Countable, Arrayable, Jsonable, \ArrayAccess, \IteratorAggregate
{
    /**
     * Should the comparison be made loosely?
     * @see Collection::diff
     * @see Collection::intersect
     */
    const LOOSE = true;

    /**
     * Should the comparison be made strictly?
     * @see Collection::diff
     * @see Collection::intersect
     */
    const STRICT = false;

    /**
     * Create a new map.
     *
     * Initialize the map with the given collection, if any. Accepts any kind
     * of collection: array, object, Traversable, another Map, etc.
     *
     * @param Map|Arrayable|Jsonable|Traversable|object|array $collection
     * @throws \InvalidArgumentException
     */
    public function __construct($collection = null)
    {
        if (null === $collection) {
            $this->array = [];
        } else {
            $this->array = $this->collection_to_array($collection);
        }
    }

    /**
     * Return a new map containing only members of this map that pass the callable.
     * 
     * An all-purpose "grep". You give a function deciding whether an element is
     * in or out, and this returns a new map of those that are in. Ex, find the
     * odd numbers:
     *
     * ```
     * $nums = new Map(range(0, 3));
     * $odds = $m->all('return 1 == ($v % 2);');
     * ``` 
     *
     * @param callable|string $code
     * @return Map
     */
    public function grep($code)
    {
        $map = new static();
        foreach ($this->array as $key => $value) {
            if ($this->passes($this->call($callable, $value, $key))) {
                $map[$key] = $value;
            }
        }
        return $map;
    }

    // TODO:add first and last similar to all

    /**
     * Determine if a key exists the map.
     *
     * This is the object method equivalent of the magic isset($map[$key]);
     *
     * @param mixed $key
     * @return bool
     */
    public function contains($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Get the value corresponding to the given key.
     *
     * If the key does not exist in the map, return the default.
     *
     * This is the object method equivalent of the magic $map[$key].
     *
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->offsetGet($key);
        }
        return $default;
    }

    /**
     * Set a key and its corresponding value into the map.
     *
     * This is the object method equivalent of the magic $map[$key] = 'foo'.
     *
     * @param mixed $key
     * @param mixed $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->offsetSet($key, $value);
        return $this;
    }

    /**
     * Remove a key and its corresponding value from the map.
     *
     * This is the object method equivalent of the magic unset($map[$key]);
     *
     * @param mixed $key
     * @return $this
     */
    public function forget($key)
    {
        $this->offsetUnset($key);
        return $this;
    }

    /**
     * Determine if any key and their values have been set into the map.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return 0 === $this->count();
    }

    /**
     * Return a new map containing those keys and values that are not present in
     * the given collection.
     *
     * If comparison is loose, then only those elements whose values match will
     * be removed.  Otherwise, comparison is strict, and elements whose keys 
     * and values match will be removed.
     *
     * @param Map|Arrayable|Jsonable|Traversable|object|array $collection
     * @param enum $comparison
     * @return Map
     */
    public function diff($collection, $comparison = Map::LOOSE)
    {
        $func = ($comparison === Map::LOOSE ? 'array_diff' : 'array_diff_assoc');
        return new static(
            $func($this->array, $this->collection_to_array($collection))
        );
    }

    /**
     * Return a new map containing those keys and values that are present in
     * the given collection.
     *
     * If comparison is loose, then only those elements whose value match will
     * be included.  Otherise, comparison is strict, and elements whose keys &
     * values match will be included.
     *
     * @param Map|Arrayable|Jsonable|Traversable|object|array $collection
     * @param enum $comparison
     * @return Map
     */
    public function intersect($collection, $comparison = Map::LOOSE)
    {
        $func = ($comparison === Map::LOOSE ? 'array_intersect' : 'array_intersect_assoc');
        return new static(
            $func($this->array, $this->collection_to_array($collection))
        );
    }

    /**
     * Execute the given code over each element of the map. The code receives
     * the value and then the key as formal parameters.
     *
     * The items are walked in the order they exist in the map. If the code
     * fails, then the iteration halts. This means you must explicitly return a
     * value, because otherwise PHP returns null which we interpret as failure.
     * Note that values can be modified from the callback, but not keys.
     *
     * Example:
     * ```
     * $map->each(function (&$value, $key) { $value++; return true; })->sum();
     * $map->each('$v++; return true;')->sum();
     * ```
     *
     * @param callable|string $code
     * @return $this
     */
    public function each($code)
    {
        foreach ($this->array as $key => $value) {
            if (! $this->passes($this->call($code, $value, $key))) {
                break;
            }
        }
        return $this;
    }

    /**
     * Returns a new map, where elements from this map have been placed into 
     * new map elements. The return value of the code determines the key for
     * each new bucket.
     *
     * The code is called for each item in the map. The code receives the value
     * and key, respectively.  The code may return a scalar key and that scalar
     * becomes the key for a new map, into which that element is placed. If the
     * code returns a non-scalar, it explodes.
     *
     * @param callable|string $code
     * @return Map
     * @throws \UnexpectedValueException
     */
    public function partition($code)
    {
        $array = [];
        foreach ($this->array as $key => $value) {
            $partition = $this->call($code, $value, $key);
            if (is_scalar($partition)) {
                if (! array_key_exists($partition, $array)) {
                    $array[$partition] = new static;
                }
                $array[$partition]->set($key, $value);
            } else {
                throw \UnexpectedValueException('code must return a scalar key');
            }
        }
        return new static($array);
    }

    // ------------------------------------------------------------------------
    // implements \Countable

    /**
     * Count the number of items in the map.
     *
     * @return int
     */
    public function count()
    {
        return count($this->array);
    }

    // ------------------------------------------------------------------------
    // implements Arrayable

    /**
     * Copy this map into an array, recursing as necessary to convert contained
     * collections into arrays.
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->array as $key => $value) {
            $array[$key] = $this->collection_to_array($value);
        }
        return $array;
    }

    // ------------------------------------------------------------------------
    // implements Jsonable

    /**
     * {@inheritDoc}
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    // ------------------------------------------------------------------------
    // implements \ArrayAccess

    /**
     * Determine if a value exists at a given key.
     *
     * @param mixed $key
     * @return bool
     */
    public function offsetExists($key)
    {
        $hash = $this->key_to_hash($key);
        return array_key_exists($hash, $this->array);
    }

    /**
     * Get a value at a given key.
     *
     * @param mixed $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        $hash = $this->key_to_hash($key);
        return $this->array[$hash];
    }

    /**
     * Set the value at a given key.
     *
     * If key is null, the value is appended to the array using numeric indexes.
     *
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (null === $key) {
            // ask PHP to give me the next index
            // http://stackoverflow.com/q/3698743/2908724
            $this->array[] = 'probe';
            $next = key($this->array);
            unset($this->array[$next]);

            // hash that
            $hash = $this->key_to_hash($next);
        } else {
            $hash = $this->key_to_hash($key);
        }
        $this->array[$hash] = $value;
    }

    /**
     * Unset the value at a given key.
     *
     * @param mixed $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->array[$key]);
    }

    // ------------------------------------------------------------------------
    // implements \IteratorAggregate

    /**
     * Get an iterator for a copy of the map.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->toArray());
    }


    // ========================================================================
    // PRIVATE API

    /**
     * The internal array representation of the map.
     * @var array
     */
    private $array = [];

    /**
     * Track string code we've made into callables.
     * @var array
     */
    private $map_code_to_callable = [];

    /**
     * Track hashes we've created for non-string keys.
     * @var array
     */
    private $map_key_to_hash = [];

    /**
     * Give me a native PHP array, regardless of what kind of collection-like
     * structure is given.
     *
     * @param Map|Traversable|Arrayable|Jsonable|object|array $items
     * @return array
     * @throws \InvalidArgumentException
     */
    private function collection_to_array($collection)
    {
        if ($collection instanceof self) {
            return $collection->toArray();

        } else if ($collection instanceof \Traversable) {
            return iterator_to_array($collection);

        } else if ($collection instanceof Arrayable) {
            return $collection->toArray();

        } else if ($collection instanceof Jsonable) {
            return json_decode($collection->toJson(), true);

        } else if (is_object($collection) || is_array($collection)) {
            return (array)$collection;

        } else {
            throw new \InvalidArgumentException(sprintf(
                'Thing of type %s not collection-like',
                gettype($collection)
            ));
        }
    }

    /**
     * Calls the given code with the given value and key as first & second argument.
     * 
     * @param callable|string $code
     * @param mixed $value
     * @param string $key
     * @throws \InvalidArgumentException
     */
    private function call($code, $value, $key)
    {
        $callable = $this->code_to_callable($code);
        return $callable($value, $key);
    }

    /**
     * Return a callable from the given code, if possible.
     *
     * When you give a callable, this returns immediately. When you give a
     * string, caches and returns an anonymous function created from that 
     * string code. Otherwise, it explodes.
     *
     * If given a string, then the value is passed in by-reference, allowing
     * that code to update the value.  Updating keys would induce undefined
     * behavior on iterations, so we don't allow that.
     *
     * @param callable|string $code
     * @throws \InvalidArgumentException
     */
    private function code_to_callable($code)
    {
        if (is_callable($code)) {
            return $code;

        } else if (is_string($code)) {
            if (! array_key_exists($code, $this->map_code_to_callable)) {
                $this->map_code_to_callable[$code] = create_function('&$v,$k', $code);
            }
            return $this->map_code_to_callable[$code];

        } else {
            throw new \InvalidArgumentException(sprintf(
                'Thing of type %s not callable-like',
                gettype($code)
            ));
        }
    }

    /**
     * Decide if the given value is considered "passing" or "failing".
     *
     * @param mixed $value
     * @return bool
     */
    private function passes($value)
    {
        return true == $value;
    }

    /**
     * Lookup the hash for the given key. If a hash does not yet exist, one is
     * created.
     *
     * @param mixed $key
     * @return string
     */
    private function key_to_hash($key)
    {
        if (array_key_exists($key, $this->map_key_to_hash)) {
            return $this->map_key_to_hash[$key];
        }

        if (is_object($key)) {
            $hash = spl_object_hash($key);

        } else if (is_numeric($key) || is_bool($key)) {
            $hash = 's_' . intval($key);

        } else if (is_string($key)) {
            $hash = "s_$key";

        } else if (is_resource($key)) {
            $hash = "r_$key";

        } else if (is_array($key)) {
            $hash = 'a_' . md5(json_encode($key));

        } else {
            return '0';
        }

        $this->map_key_to_hash[$key] = $hash;
        return $hash;
     }

     /**
      * Lookup the key for the given hash.
      *
      * @param string $hash
      * @return mixed
      */
     private function hash_to_key($hash)
     {
        foreach ($this->map_key_to_hash as $key => $candidate) {
            if ($hash === $candidate) {
                return $key;
            }
        }

        throw new \OutOfBoundsException(sprintf(
            'Hash %s hash not been created',
            $hash
        ));
     }
}
