<?php
namespace Haldayne\Boost;

/**
 * Implements a map of numbers, that is a Map whose values must all pass the
 * `is_numeric` test.
 */
class MapOfNumerics extends GuardedMapAbstract
{
    /**
     * Translate this map by the quantities given in the other collection.
     * This is like addition or subtraction.
     *
     * @param Map|Arrayable|Jsonable|Traversable|object|array $collection
     * @return $this
     * @api
     *
     * @see https://en.wikipedia.org/wiki/Translation_(geometry)
     */
    public function translate($collection)
    {
        return $this->merge(
            $collection,
            function ($a, $b) { return $a + $b; },
            0
        );
    }

    /**
     * Scale this map by the factors given in the other collection.
     * This is like multiplication or division.
     *
     * @param Map|Arrayable|Jsonable|Traversable|object|array $collection
     * @return $this
     * @api
     *
     * @see https://en.wikipedia.org/wiki/Scaling_(geometry)
     */
    public function scale($collection)
    {
        return $this->merge(
            $collection,
            function ($a, $b) { return $a * $b; },
            1
        );
    }

    /**
     * Return the sum of all elements in the map.
     *
     * @return numeric
     * @api
     */
    public function sum()
    {
        return $this->reduce(
            function ($sum, $number) { return $sum + $number; },
            0
        );
    }

    /**
     * Return the product of all elements in the map.
     *
     * @return numeric
     * @api
     */
    public function product()
    {
        return $this->reduce(
            function ($sum, $number) { return $sum * $number; },
            1
        );
    }

    /**
     * Return the arithmetic mean ("average") of all elements in the map. If
     * there are no elements, throws a \RangeException.
     *
     * @return numeric
     * @throws \RangeException
     * @api
     */
    public function mean()
    {
        if (0 < $this->count()) {
            return $this->sum() / $this->count();
        } else {
            throw new \RangeException('Map has no elements and therefore no mean');
        }
    }

    /**
     * Return the minimum value from the elements in the map. If there are no
     * elements, throws a \RangeException.
     *
     * @return numeric
     * @throws \RangeException
     * @api
     */
    public function min()
    {
        if (0 < $this->count()) {
            return min($this->toArray());
        } else {
            throw new \RangeException('Map has no elements and therefore no minimum');
        }
    }

    /**
     * Return the maximum value from the elements in the map. If there are no
     * elements, throws a \RangeException.
     *
     * @return numeric
     * @throws \RangeException
     * @api
     */
    public function max()
    {
        if (0 < $this->count()) {
            return max($this->toArray());
        } else {
            throw new \RangeException('Map has no elements and therefore no maximum');
        }
    }

    /**
     * Increment the value stored at the given key by the given delta.
     *
     * @return $this
     * @since 1.0.3
     * @api
     */
    public function increment($key, $delta = 1)
    {
        if ($this->has($key)) {
            $this->set(
                $key, 
                $this->get($key) + $delta
            );
        } else {
            $this->set($key, $delta);
        }

        return $this;
    }

    /**
     * Decrement the value stored at the given key by the given delta.
     * Implemented by simply incrementing the negative of the delta.
     *
     * @return $this
     * @since 1.0.3
     * @api
     */
    public function decrement($key, $delta = 1)
    {
        return $this->increment($key, -$delta);
    }

    // PROTECTED API

    /**
     * {@inheritDoc}
     */
    protected function allowed($value)
    {
        return is_numeric($value);
    }
}
