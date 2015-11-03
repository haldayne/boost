<?php
namespace Haldayne\Boost;

/**
 * Restricts what can be set into a Map, optionally normalizing those values
 * that can be set.
 */
abstract class GuardedMapAbstract extends Map
{
    /**
     * Decides if the given element should be allowed into the map.
     *
     * @param mixed $value
     * @return bool
     */
    abstract protected function allowed($value);

    /**
     * Set the value at a given key, provided that the value passes the
     * defined guard.
     *
     * {@inheritDoc}
     * @throws \UnexpectedValueException
     */
    public function offsetSet($key, $value)
    {
        $result = $this->allowed($value);
        if ($this->passes($result)) {
            parent::offsetSet($key, $this->normalize($value));
        } else {
            throw new \UnexpectedValueException('Value forbidden in this map');
        }
    }

    // PROTECTED API

    /**
     * Normalize the value before storing.
     *
     * This default implementation does nothing.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function &normalize(&$value)
    {
        return $value;
    }
}
