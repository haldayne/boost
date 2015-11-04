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
     * Calculate letter or word frequency.
     */
    public function frequency()
    {
        return $this
            ->transform( // convert each word to a frequency count of letters
                function () { return new MapOfCollections(); },
                function ($word) {
                    $freqs = new MapOfInts(count_chars($word, 1));
                    $freqs->rekey('chr($_1)');
                    $strategy = new TransformStrategy();
                    $strategy->push($freqs);
                    return $strategy;
                },
            )
            ->reduce( // sum up the individual word's frequency counts
                function ($totals, $counts) {
                    return $totals->translate($counts);
                },
                new MapOfInts()
            )
        ;
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
