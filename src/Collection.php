<?php
namespace Haldayne\DataStructure;

use Haldayne\DataStructure\Contract\Arrayable;
use Haldayne\DataStructure\Contract\Jsonable;

class Collection implements \Countable, Arrayable, Jsonable, \ArrayAccess, \IteratorAggregate
{
    /**
     * Should the comparison be made loosely?
     * @const
     * @see Collection::diff
     * @see Collection::intersect
     */
    const LOOSE_COMPARISON = true;

    /**
     * Should the comparison be made strictly?
     * @const
     * @see Collection::diff
     * @see Collection::intersect
     */
    const STRICT_COMPARISON = false;

    /**
     * Create a new collection. If items are given, these are copied in to
     * initialize the collection.
     *
     * @param Collection|Arrayable|Jsonable|object|array $items
     * @throws \InvalidArgumentException
     */
    public function __construct($items = null)
    {
        $this->items = (null === $items ? [] : $this->getAsArray($items));
    }

    /**
     * Get all of the items in the collection, as an array.
     *
     * @return array
     */
    public function all()//TODO:change to accept an optional callback
    {
        return $this->items;// TODO:change to collection
    }

    // TODO:add first and last similar to all

    /**
     * Determine if an item exists in the collection, by key.
     *
     * This is the object method equivalent of the magic isset($collection[$key]);
     *
     * @param scalar $key
     * @return bool
     */
    public function contains($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Get an item from the collection, by key.
     *
     * If the key does not exist in the collection, return the default.
     *
     * This is the object method equivalent of the magic $collection[$key].
     *
     * @param scalar $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->items[$key];
        }

        return $default;
    }

    /**
     * Set an item into the collection, by key.
     *
     * This is the object method equivalent of the magic $collection[$key] = 'foo'.
     *
     * @param scalar $key
     * @return $this
     */
    public function set($key, $value)
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * Remove an item from the collection by key.
     *
     * This is the object method equivalent of the magic unset($collection[$key]);
     *
     * @param scalar $key
     * @return $this
     */
    public function forget($key)
    {
        $this->offsetUnset($key);

        return $this;
    }

    /**
     * Determine if the collection is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return 0 === $this->count();
    }

    /**
     * Get the items in the collection that are not present in the given items.
     *
     * If comparison is loose, then only items whose values match will be
     * removed.  If comparison is strict, then items whose key *and* value
     * match will be removed.
     *
     * @param Collection|Arrayable|Jsonable|object|array $items
     * @return static
     */
    public function diff($items, $comparison = Collection::LOOSE_COMPARISON)
    {
        $func = ($comparison === Collection::LOOSE_COMPARISON ? 'array_diff' : 'array_diff_assoc');
        return new static($func($this->items, $this->getAsArray($items)));
    }

    /**
     * Intersect the collection with the given items.
     *
     * If comparison is loose, then items whose values match will be be included,
     * regardless of whether key matches.  If comparison is strict, then only
     * items whose key *and* value match will be included.
     *
     * @param Collection|Arrayable|Jsonable|object|array $items
     * @return static
     */
    public function intersect($items, $comparison = Collection::LOOSE_COMPARISON)
    {
        $func = ($comparison === Collection::LOOSE_COMPARISON ? 'array_intersect' : 'array_intersect_assoc');
        return new static($func($this->items, $this->getAsArray($items)));
    }

    /**
     * Execute a callback over each item.
     *
     * The items are walked in the order they exist in the colleciton.  If the
     * callback returns exactly false, then the iteration halts.
     *
     * @param callable $callback
     * @return $this
     */
    public function each(callable $callback)
    {
        foreach ($this->items as $key => $value) {
            if (false === $callback($value, $key)) {
                break;
            }
        }

        return $this;
    }

    /**
     * Put items into buckets using a callback to determine the buckets.
     *
     * The partitioner is called for each item in the collection. The partitioner
     * receives the item value and key as its arguments, respectively.  The
     * partitioner may return a scalar key, and the item is then added to the
     * result Collection using the returned key.
     *
     * @param callable $partitioner
     * @return static
     */
    public function partition(callable $partitioner)
    {
        $collection = [];

        foreach ($this->items as $key => $value) {
            $partition = $partitioner($value, $key);
            if (is_scalar($partition)) {
                $collection[$partition][$key] = $value;
            } else {
                throw \UnexpectedValueException('partitioner must return a scalar key');
            }
        }

        return new static($collection);
    }

    // ------------------------------------------------------------------------
    // implements \Countable

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    // ------------------------------------------------------------------------
    // implements Arrayable

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return $this->items;
    }

    // ------------------------------------------------------------------------
    // implements Jsonable

    /**
     * {@inheritDoc}
     */
    public function toJson($options = 0)
    {
        return json_encode($this->items, $options);
    }

    // ------------------------------------------------------------------------
    // implements \ArrayAccess

    /**
     * Determine if an item exists at an offset.
     *
     * @param scalar $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Get an item at a given offset.
     *
     * @param scalar $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param scalar $key
     * @param mixed $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (null === $key) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param scalar $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->items[$key]);
    }

    // ------------------------------------------------------------------------
    // implements \IteratorAggregate

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }


    // ========================================================================
    // PRIVATE API

    /**
     * The items contained in the collection.
     *
     * @var array
     */
    private $items = [];

    /**
     * Give me a native PHP array, regardless of what kind of collection-like
     * structure is given.
     *
     * @param Collection|Arrayable|Jsonable|object|array $items
     * @return array
     * @throws \InvalidArgumentException
     */
    private function getAsArray($items)
    {
        if ($items instanceof self) {
            return $items->all();

        } else if ($items instanceof Arrayable) {
            return $items->toArray();

        } else if ($items instanceof Jsonable) {
            return json_decode($items->toJson(), true);

        } else if (is_object($items) || is_array($items)) {
            return (array)$items;

        } else {
            throw new \InvalidArgumentException(sprintf(
                'Items of type %s not array-like',
                gettype($items)
            ));
        }
    }
}
