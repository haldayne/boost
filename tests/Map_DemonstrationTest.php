<?php
namespace Haldayne\Boost;

/**
 * Demonstrates Map behavior.
 */
class Map_DemonstrationTest extends \PHPUnit_Framework_TestCase
{
    public function test_fluent_conversion()
    {
        $words = new MapOfStrings([ 'foo', 'bar' ]);
        $this->assertSame(
            6,
            $words->map('strlen($_0)')->into(new MapOfInts)->sum()
        );
    }

    public function test_letter_frequency()
    {
        $words = new Map([ 'aardvark', 'roads', 'sparks' ]);
        $freqs = $words
            ->map( // convert each word to a frequency count of letters
                function ($word) {
                    return new MapOfInts(count_chars($word, 1));
                }
            )
            ->reduce( // sum up the individual word's frequency counts
                function ($totals, $counts) {
                    return $totals->translate($counts);
                },
                new MapOfInts()
            )
            ->rekey('chr($_1)') // change to keying by the letter, not the ASCII byte value
        ;
        $this->assertSame(
            [ 'a' => 5, 'd' => 2, 'k' => 2, 'r' => 4, 'v' => 1, 'o' => 1, 's' => 3, 'p' => 1 ],
            $freqs->toArray()
        );
    }
}
