<?php
namespace Haldayne\Boost;

use Haldayne\Boost\Contract\Arrayable;
use Haldayne\Boost\Contract\Jsonable;
use Haldayne\Fox\Expression;

/**
 * API improvements for PHP associative arrays. Features a consistent fluent
 * interface, keys of any type, a short-hand syntax for filtering expressions.
 *
 * Methods accepting a `$collection` may receive any of these types:
 *   - array
 *   - object
 *   - \Traversable
 *   - \Haldayne\Boost\Map
 *   - \Haldayne\Boost\Contract\Arrayable
 *   - \Haldayne\Boost\Contract\Jsonable
 *
 * Methods accept a `$key` may be of any type: boolean, integer, float,
 * string, array, object, closure, or resource.
 * 
 * Methods accepting an `$expression` may receive a [PHP callable][1] or a
 * string. When given a string, the library wraps an anonymous function around
 * the string code body and returns the result. By way of example, these
 * are equivalent and both acceptable as an `$expression`:
 *   - `$_0 < $_1`
 *   - `function ($_0, $_1) { return $_0 < $_1; }
 *
 * Expressions lets you write extremely compact code for filtering, at the
 * one-time run-time cost of converting the string to the body of an anonymous
 * function.
 *
 * Expressions, whether given as a callable or a string, receive two formal
 * arguments: the current value and the current key.  Note that, inside string
 * expressions, these are represented by `$_0` and `$_1` respectively.
 */
class Map implements \Countable, Arrayable, Jsonable, \ArrayAccess, \IteratorAggregate
{
    /**
     * Should the comparison be made loosely?
     * @see Map::diff
     * @see Map::intersect
     * @api
     */
    const LOOSE = true;

    /**
     * Should the comparison be made strictly?
     * @see Map::diff
     * @see Map::intersect
     * @api
     */
    const STRICT = false;

    /**
     * Create a new map.
     *
     * Initialize the map with the given collection, which can be any type
     * that is "collection-like": array, object, Traversable, another Map,
     * etc.
     *
     * @param Map|Arrayable|Jsonable|Traversable|object|array $collection
     * @throws \InvalidArgumentException
     * @api
     */
    public function __construct($collection = null)
    {
        if (null !== $collection) {
            $array = $this->collection_to_array($collection);
            array_walk(
                $array,
                function ($v, $k) { $this->set($k, $v); }
            );
        }
    }

    /**
     * Get the keys of this map as a new map.
     * 
     * @return new Map
     * @api
     * @since 1.0.5
     */
    public function keys()
    {
        $map = new Map;
        foreach (array_keys($this->array) as $hash) {
            $map[] = $this->hash_to_key($hash);
        }
        return $map;
    }

    /**
     * Create a new map containing all members from this map whose elements
     * satisfy the expression.
     * 
     * The expression decides whether an element is in or out. If the
     * expression returns boolean false, the element is out.  Otherwise, it's
     * in.
     *
     * ```
     * $nums = new Map(range(0, 9));
     * $even = $nums->all(function ($val, $key) { return 0 == $val % 2; });
     * $odds = $nums->all('$_0 & 1');
     * ```
     *
     * @param callable|string $expression
     * @return new static
     * @api
     */
    public function all($expression)
    {
        return $this->grep($expression);
    }

    /**
     * Apply the filter to every element, creating a new map with only those
     * elements from the original map that do not fail this filter.
     *
     * The filter expressions receives two arguments:
     *   - The current value
     *   - The current key
     *
     * If the filter returns exactly boolean false, the element is not copied
     * into the new map.  Otherwise, it is.  Keys from the original map carry
     * into the new map.
     *
     * @param callable|string $expression
     * @return new static
     * @api
     */
    public function filter($expression)
    {
        $new = new static;

        $this->walk(function ($v, $k) use ($expression, $new) {
            $result = $this->call($expression, $v, $k);
            if ($this->passes($result)) {
                $new[$k] = $v;
            }
        });

        return $new;
    }

    /**
     * Return a new map containing the first N elements passing the
     * expression.
     * 
     * Like `find`, but stop after finding N elements from the front. Defaults
     * to N = 1.
     *
     * ```
     * $nums = new Map(range(0, 9));
     * $odd3 = $nums->first('1 == ($_0 % 2)', 3); // first three odds
     * ``` 
     *
     * @param callable|string $expression
     * @param int $n
     * @return new static
     * @api
     */
    public function first($expression, $n = 1)
    {
        if (is_numeric($n) && intval($n) <= 0) {
            throw new \InvalidArgumentException('Argument $n must be whole number');
        }
        return $this->grep($expression, intval($n));
    }

    /**
     * Return a new map containing the last N elements passing the expression.
     * 
     * Like `first`, but stop after finding N elements from the *end*.
     * Defaults to N = 1.
     *
     * ```
     * $nums = new Map(range(0, 9));
     * $odds = $nums->last('1 == ($_0 % 2)', 2); // last two odd numbers
     * ``` 
     *
     * @param callable|string $expression
     * @param int $n
     * @return new static
     * @api
     */
    public function last($expression, $n = 1)
    {
        if (is_numeric($n) && intval($n) <= 0) {
            throw new \InvalidArgumentException('Argument $n must be whole number');
        }
        return $this->grep($expression, -intval($n));
    }

    /**
     * Test if every element passes the expression.
     *
     * @param callable|string $expression
     * @return bool
     * @api
     */
    public function every($expression)
    {
        return $this->grep($expression)->count() === $this->count();
    }

    /**
     * Test if at least one element passes the expression.
     *
     * @param callable|string $expression
     * @return bool
     * @api
     */
    public function some($expression)
    {
        return 1 === $this->first($expression)->count();
    }

    /**
     * Test that no elements pass the expression.
     *
     * @param callable|string $expression
     * @return bool
     * @api
     */
    public function none($expression)
    {
        return 0 === $this->first($expression)->count();
    }

    /**
     * Determine if a key exists the map.
     *
     * This is the object method equivalent of the magic isset($map[$key]);
     *
     * @param mixed $key
     * @return bool
     * @api
     */
    public function has($key)
    {
        $hash = $this->key_to_hash($key);
        return array_key_exists($hash, $this->array);
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
     * @api
     */
    public function get($key, $default = null)
    {
        $hash = $this->key_to_hash($key);
        if (array_key_exists($hash, $this->array)) {
            return $this->array[$hash];
        } else {
            return $default;
        }
    }

    /**
     * Set a key and its corresponding value into the map.
     *
     * This is the object method equivalent of the magic $map[$key] = 'foo'.
     *
     * @param mixed $key
     * @param mixed $value
     * @return $this
     * @api
     */
    public function set($key, $value)
    {
        $hash = $this->key_to_hash($key);
        $this->array[$hash] = $value;
        return $this;
    }

    /**
     * Remove a key and its corresponding value from the map.
     *
     * This is the object method equivalent of the magic unset($map[$key]);
     *
     * @param mixed $key
     * @return $this
     * @api
     */
    public function forget($key)
    {
        unset($this->array[$this->key_to_hash($key)]);
        return $this;
    }

    /**
     * Determine if any key and their values have been set into the map.
     *
     * @return bool
     * @api
     */
    public function isEmpty()
    {
        return 0 === $this->count();
    }

    /**
     * Return a new map containing those keys and values that are not present
     * in the given collection.
     *
     * If comparison is loose, then only those elements whose values match will
     * be removed.  Otherwise, comparison is strict, and elements whose keys 
     * and values match will be removed.
     *
     * @param Map|Arrayable|Jsonable|Traversable|object|array $collection
     * @param enum $comparison
     * @return new static
     * @api
     */
    public function diff($collection, $comparison = Map::LOOSE)
    {
        $func = ($comparison === Map::LOOSE ? 'array_diff' : 'array_diff_assoc');
        return new static(
            $func($this->toArray(), $this->collection_to_array($collection))
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
     * @return new static
     * @api
     */
    public function intersect($collection, $comparison = Map::LOOSE)
    {
        $func = ($comparison === Map::LOOSE ? 'array_intersect' : 'array_intersect_assoc');
        return new static(
            $func($this->toArray(), $this->collection_to_array($collection))
        );
    }

    /**
     * Groups elements of this map based on the result of an expression.
     *
     * Calls the expression for each element in this map. The expression
     * receives the value and key, respectively.  The expression may return
     * any value: this value is the grouping key and the element is put into
     * that group.
     *
     * ```
     * $nums = new Map(range(0, 9));
     * $part = $nums->partition(function ($value, $key) {
     *    return 0 == $value % 2 ? 'even' : 'odd';
     * });
     * var_dump(
     *     $part['odd']->count(), // 5
     *     array_sum($part['even']->toArray()) // 20
     * );
     * ```
     *
     * @param callable|string $expression
     * @return MapOfCollections
     * @api
     */
    public function partition($expression)
    {
        $outer = new MapOfCollections;
        $proto = new static;

        $this->walk(function ($v, $k) use ($expression, $outer, $proto) {
            $partition = $this->call($expression, $v, $k);

            $inner = $outer->has($partition) ? $outer->get($partition) : clone $proto;
            $inner->set($k, $v);

            $outer->set($partition, $inner);
        });

        return $outer;
    }

    /**
     * Walk the map, applying the expression to every element, transforming
     * them into a new map.
     *
     * ```
     * $nums = new Map(range(0, 9));
     * $doubled = $nums->map('$_0 * 2');
     * ```
     *
     * The expression receives two arguments:
     *   - The current value in `$_0`
     *   - The current key in `$_1`
     *
     * The keys in the resulting map will be the same as the keys in the 
     * original map: only the values have (potentially) changed.
     *
     * Recommended to use this method when you are mapping from one type to
     * the same type: int to int, string to string, etc. If you are changing
     * types, use the more powerful `transform` method.
     *
     * @param callable|string $expression
     * @return Map
     * @api
     */
    public function map($expression)
    {
        $new = new self;

        $this->walk(function ($v, $k) use ($expression, $new) {
            $new[$k] = $this->call($expression, $v, $k);
        });

        return $new;
    }

    /**
     * Walk the map, applying a reducing expression to every element, so as to
     * reduce the map to a single value.
     *
     * The `$reducer` expression receives three arguments:
     *   - The current reduction (`$_0`)
     *   - The current value (`$_1`)
     *   - The current key (`$_2`)
     * 
     * The initial value, if given or null if not, is passed as the current
     * reduction on the first invocation of `$reducer`. The return value from
     * `$reducer` then becomes the new, current reduced value.
     *
     * ```
     * $nums = new Map(range(0, 3));
     * $sum = $nums->reduce('$_0 + $_1');
     * // $sum == 6
     * ```
     *
     * If `$finisher` is a callable or string expression, then it will be
     * called last, after iterating over all elements. It will be passed
     * reduced value. The `$finisher` must return the new final value.
     *
     * @param callable|string $reducer
     * @param mixed $initial
     * @param callable|string|null $finisher
     * @return mixed
     * @api
     *
     * @see http://php.net/manual/en/function.array-reduce.php
     */
    public function reduce($reducer, $initial = null, $finisher = null)
    {
        $reduced = $initial;
        $this->walk(function ($value, $key) use ($reducer, &$reduced) {
            $reduced = $this->call($reducer, $reduced, $value, $key);
        });

        if (null === $finisher) {
            return $reduced;
        } else {
            return $this->call($finisher, $reduced);
        }
    }

    /**
     * Change the key for every element in the map using an expression to
     * calculate the new key.
     *
     * ```
     * $keyed_by_bytecode = new Map(count_chars('war of the worlds', 1));
     * $keyed_by_letter   = $keyed_by_bytecode->rekey('chr($_1)');
     * ```
     *
     * @param callable|string $expression
     * @return new static
     * @api
     */
    public function rekey($expression)
    {
        $new = new static;

        $this->walk(function ($v, $k) use ($expression, $new) {
            $new_key = $this->call($expression, $v, $k);
            $new[$new_key] = $v;
        });

        return $new;
    }

    /**
     * Merge the given collection into this map.
     *
     * The merger callable decides how to merge the current map's value with
     * the given collection's value.  The merger callable receives two
     * arguments:
     *   - This map's value at the given key
     *   - The collection's value at the given key
     *
     * If the current map does not have a value for a key in the collection,
     * then the default value is assumed.
     *
     * @param Map|Arrayable|Jsonable|Traversable|object|array $collection
     * @param callable $merger
     * @param mixed $default
     * @return $this
     * @api
     */
    public function merge($collection, callable $merger, $default = null)
    {
        $array = $this->collection_to_array($collection);
        foreach ($array as $key => $value) {
            $current = $this->get($key, $default);
            $this->set($key, $merger($current, $value));
        }
        return $this;
    }

    /**
     * Flexibly and thoroughly change this map into another map.
     *
     * ```
     * // transform a word list into a map of word to frequency in the list
     * use Haldayne\Boost\Map;
     * $words   = new Map([ 'bear', 'bee', 'goose', 'bee' ]);
     * $lengths = $words->transform(
     *     function (Map $new, $word) { 
     *         if ($new->has($word)) {
     *             $new->set($word, $new->get($word)+1);
     *         } else {
     *             $new->set($word, 1);
     *         }
     *     }
     * );
     * ```
     *
     * Sometimes you need to create one map from another using a strategy
     * that isn't one-to-one. You may need to change keys. You may need to
     * add multiple elements. You may need to delete elements. You may need
     * to change from a map to a number.
     * 
     * Whatever the case, the other simpler methods in Map don't quite fit the
     * problem. What you need, and what this method provides, is a complete
     * machine to transform this map into something else:
     *
     * ```
     * // convert a word list into a count of unique letters in those words
     * use Haldayne\Boost\Map;
     * $words   = new Map([ 'bear', 'bee', 'goose', 'bee' ]);
     * $letters = $words->transform(
     *     function ($frequencies, $word) {
     *         foreach (count_chars($word, 1) as $byte => $frequency) {
     *             $letter = chr($byte);
     *             if ($frequencies->has($letter)) {
     *                 $new->set($letter, $frequencies->get($letter)+1);
     *             } else {
     *                 $new->set($letter, 1);
     *             }
     *         }
     *     },
     *     function (Map $original) { return new MapOfIntegers(); },
     *     function (MapOfIntegers $new) { return $new->sum(); }
     * );
     * ```
     *
     * This method accepts three callables
     * 1. `$creator`, which is called first with the current map, performs any
     * initialization needed.  The result of this callable will be passed to
     * all the other callables.  If no creator is given, then use a default
     * one that returns an empty Map.
     * 
     * 2. `$transformer`, which is called for every element in this map and
     * receives the initialized value, the current value, and the current key
     * in that order. The transformer should modify the initialized value
     * appropriately. Often this means adding to a new map zero or more
     * tranformed values.
     *
     * 3. `$finisher`, which is called last, receives the initialized value
     * that was modified by the transformer calls. The finisher may transform
     * that value once more as needed. If no finisher given, then no finishing
     * step is made.
     *
     * @param callable $tranformer
     * @param callable|null $creator
     * @param callable|null $finisher
     * @return mixed
     * @api
     */
    public function transform(callable $transformer, callable $creator = null, callable $finisher = null)
    {
        // create the initial object, using as needed the default creator function
        if (null === $creator) {
            $creator = function (Map $original) { return new Map(); };
        }
        $initial = $creator($this);

        // transform the initial value using the transformer
        $this->walk(function ($value, $key) use ($transformer, &$initial) {
            $transformer($initial, $value, $key);
        });

        // finish up
        if (null === $finisher) {
            return $initial;
        } else {
            return $finisher($initial);
        }
    }

    /**
     * Put all of this map's elements into the target and return the target.
     *
     * ```
     * $words = new MapOfStrings([ 'foo', 'bar' ]);
     * $words->map('strlen($_0)')->into(new MapOfInts)->sum(); // 6
     * ```
     *
     * Use when you've mapped your elements into a different type, and you
     * want to fluently perform operations on the new type. In the example,
     * the sum of the words' lengths was calculated.
     *
     * @return $target
     * @api
     */
    public function into(Map $target)
    {
        $this->walk(function ($value, $key) use ($target) {
            $target->set($key, $value);
        });
        return $target;
    }

    /**
     * Treat the map as a stack and push an element onto its end.
     *
     * @return $this
     * @api
     */
    public function push($element)
    {
        // ask PHP to give me the next index
        // http://stackoverflow.com/q/3698743/2908724
        $this->array[] = 'probe';
        end($this->array);
        $next = key($this->array);
        unset($this->array[$next]);

        // hash that and store
        $this->set($next, $element);

        return $this;
    }

    /**
     * Treat the map as a stack and pop an element off its end.
     *
     * @return mixed
     * @api
     */
    public function pop()
    {
        // get last key of array
        end($this->array);
        $hash = key($this->array);

        // destructively get the element there
        $element = $this->array[$hash];
        unset($this->array[$hash]);

        // return it
        return $element;
    }

    // -----------------------------------------------------------------------
    // implements \Countable

    /**
     * Count the number of items in the map.
     *
     * @return int
     * @api
     */
    public function count()
    {
        return count($this->array);
    }

    // -----------------------------------------------------------------------
    // implements Arrayable

    /**
     * Copy this map into an array, recursing as necessary to convert
     * contained collections into arrays.
     *
     * @api
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->array as $hash => $value) {
            $key = $this->hash_to_key($hash);
            if ($this->is_collection_like($value)) {
                $array[$key] = $this->collection_to_array($value);
            } else {
                $array[$key] = $value;
            }
        }
        return $array;
    }

    // -----------------------------------------------------------------------
    // implements Jsonable

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    // -----------------------------------------------------------------------
    // implements \ArrayAccess

    /**
     * Determine if a value exists at a given key.
     *
     * @param mixed $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get a value at a given key.
     *
     * @param mixed $key
     * @return mixed|null
     */
    public function offsetGet($key)
    {
        return $this->get($key, null);
    }

    /**
     * Set the value at a given key.
     *
     * If key is null, the value is appended to the array using numeric
     * indexes, just like native PHP. Unlike native-PHP, $key can be of any
     * type: boolean, int, float, string, array, object, closure, resource.
     *
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (null === $key) {
            $this->push($value);
        } else {
            $this->set($key, $value);
        }
    }

    /**
     * Unset the value at a given key.
     *
     * @param mixed $key
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->forget($key);
    }

    // -----------------------------------------------------------------------
    // implements \IteratorAggregate

    /**
     * Get an iterator for a copy of the map.
     *
     * @return \ArrayIterator
     * @api
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->toArray());
    }


    // =======================================================================
    // PROTECTED API

    /**
     * Decide if the given result is considered "passing" or "failing".
     *
     * This method provides a definitive reference for what this and all
     * derived classes consider passing:
     *   - if the result is strictly false, the result "failed"
     *   - otherwise, the result "succeeded"
     *
     * @param mixed $result
     * @return bool
     */
    protected function passes($result)
    {
        return false === $result ? false : true;
    }

    /**
     * Decide if the given value is considered collection-like.
     *
     * @param mixed $value
     * @return bool
     */
    protected function is_collection_like($value)
    {
        if ($value instanceof self) {
            return true;

        } else if ($value instanceof \Traversable) {
            return true;

        } else if ($value instanceof Arrayable) {
            return true;

        } else if ($value instanceof Jsonable) {
            return true;

        } else if (is_object($value) || is_array($value)) {
            return true;

        } else {
            return false;
        }
    }

    /**
     * Give me a native PHP array, regardless of what kind of collection-like
     * structure is given.
     *
     * @param Map|Traversable|Arrayable|Jsonable|object|array $items
     * @return array|boolean
     * @throws \InvalidArgumentException
     */
    protected function collection_to_array($collection)
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
                '$collection has type %s, which is not collection-like',
                gettype($collection)
            ));
        }
    }

    /**
     * Finds elements for which the given code passes, optionally limited to a
     * maximum count.
     *
     * If limit is null, no limit on number of matches. If limit is positive,
     * return that many from the front of the array. If limit is negative,
     * return that many from the end of the array.
     *
     * @param callable|string $expression
     * @param int|null $limit
     * @return new static
     */
    protected function grep($expression, $limit = null)
    {
        // initialize our return map and book-keeping values
        $map = new static;
        $bnd = empty($limit) ? null : abs($limit);
        $cnt = 0;

        // define a helper to add matching values to our new map, stopping when
        // any designated limit is reached
        $helper = function ($value, $key) use ($expression, $map, $bnd, &$cnt) {
            if ($this->passes($this->call($expression, $value, $key))) {
                $map->set($key, $value);
                if (null !== $bnd && $bnd <= ++$cnt) {
                    return false;
                }
            }
        };

        // walk the array in the right direction
        if (0 <= $limit) {
            $this->walk($helper);

        } else {
            $this->walk_backward($helper);
        }

        return $map;
    }

    /**
     * Execute the given code over each element of the map. The code receives
     * the value by reference and then the key as formal parameters.
     *
     * The items are walked in the order they exist in the map. If the code
     * returns boolean false, then the iteration halts. Values can be modified
     * from within the callback, but not keys.
     *
     * Example:
     * ```
     * $map->each(function (&$value, $key) { $value++; return true; })->sum();
     * ```
     *
     * @param callable $code
     * @return $this
     */
    protected function walk(callable $code)
    {
        foreach ($this->array as $hash => &$value) {
            $key = $this->hash_to_key($hash);
            if (! $this->passes($this->call($code, $value, $key))) {
                break;
            }
        }
        return $this;
    }

    /**
     * Like `walk`, except walk from the end toward the front.
     *
     * @param callable $code
     * @return $this
     */
    protected function walk_backward(callable $code)
    {
        for (end($this->array); null !== ($hash = key($this->array)); prev($this->array)) {
            $key   = $this->hash_to_key($hash);
            $value =& current($this->array);
            if (! $this->passes($this->call($code, $value, $key))) {
                break;
            }
        }
        return $this;
    }

    // =======================================================================
    // PRIVATE API

    /**
     * The internal array representation of the map.
     * @var array
     */
    private $array = [];

    /**
     * Track hashes we've created for non-string keys.
     * @var array
     */
    private $map_hash_to_key = [];

    /**
     * Lookup the hash for the given key. If a hash does not yet exist, one is
     * created.
     *
     * @param mixed $key
     * @return string
     * @throws \InvalidArgumentException
     */
    private function key_to_hash($key)
    {
        if (null === $key) {
            $hash = 'null';

        } else if (is_int($key)) {
            $hash = $key;

        } else if (is_float($key)) {
            $hash = "f_$key";

        } else if (is_bool($key)) {
            $hash = "b_$key";

        } else if (is_string($key)) {
            $hash = "s_$key";

        } else if (is_object($key) || is_callable($key)) {
            $hash = spl_object_hash($key);

        } else if (is_array($key)) {
            $hash = 'a_' . md5(json_encode($key));

        } else if (is_resource($key)) {
            $hash = "r_$key";

        } else {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported key type "%s"',
                gettype($key)
            ));
        }

        $this->map_hash_to_key[$hash] = $key;
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
        if (array_key_exists($hash, $this->map_hash_to_key)) {
            return $this->map_hash_to_key[$hash];
        } else {
            throw new \OutOfBoundsException(sprintf(
                'Hash "%s" has not been created',
                $hash
            ));
        }
     }

    /**
     * Call the expression with the arguments.
     * 
     * @param callable|string $expression
     * @throws \InvalidArgumentException
     */
    private function call($expression)
    {
        $callable = new Expression($expression);
        return call_user_func_array($callable, array_slice(func_get_args(), 1));
    }
}
