<?php
namespace Haldayne\Boost;

/**
 * Implements a map of strings, that is a Map whose values must all pass the
 * `is_string` test.
 */
class MapOfStrings extends GuardedMapAbstract
{
    /**
     * Join all the strings in this map with the separator between them.
     *
     * @param string $separator
     * @return string
     */
    public function join($separator)
    {
        return $this->reduce(
            function ($joined, $string) use ($separator) {
                return ('' === $joined ? '' : $joined . $separator) . $string;
            },
            ''
        );
    }

    /**
     * Factory a new map by splitting a string at the given separator.
     *
     * @param string $separator
     * @param string $string
     * @return static
     */
    public static function split($separator, $string)
    {
        return new static(explode($separator, $string));
    }

    // PROTECTED API

    /**
     * {@inheritDoc}
     */
    protected function allowed($value)
    {
        return is_string($value);
    }
}
