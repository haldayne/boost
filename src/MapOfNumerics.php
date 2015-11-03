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
     *
     * @param Map|Arrayable|Jsonable|Traversable|object|array $collection
     * @return $this
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
     *
     * @param Map|Arrayable|Jsonable|Traversable|object|array $collection
     * @return $this
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
     */
    public function product()
    {
        return $this->reduce(
            function ($sum, $number) { return $sum * $number; },
            1
        );
    }

    /**
     * Return the arithmetic mean ("average") of all elements in the map.
     *
     * @return numeric
     */
    public function mean()
    {
        return $this->sum() / $this->count();
    }

    /**
     * Return the minimum value from the elements in the map. If there are no
     * elements, throws a \RangeException.
     *
     * @return numeric
     * @throws \RangeException
     */
    public function min()
    {
        if (0 < $this->count()) {
            return false;
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
     */
    public function max()
    {
        if (0 < $this->count()) {
            return false;
        } else {
            throw new \RangeException('Map has no elements and therefore no maximum');
        }
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
