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
     * Calculate frequency of letters in all strings.
     *
     * Counts how many times each letter occurs within every string in this
     * map.  Returns a new Map of letter to number of occurrences.  Only
     * letters that appear will be in the resulting map.
     *
     * @return \Haldayne\Boost\MapOfIntegers
     */
    public function letter_frequency()
    {
        return $this->transform(
            function ($frequencies, $word) {
                foreach (count_chars($word, 1) as $byte => $frequency) {
                    $letter = chr($byte);
                    if ($frequencies->has($letter)) {
                        $frequencies->set($letter, $frequencies->get($letter)+1);
                    } else {
                        $frequencies->set($letter, 1);
                    }
                }
            },
            function (Map $original) { return new MapOfInts(); }
        );
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
