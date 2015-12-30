<?php
namespace Haldayne\Boost;

/**
 * Demonstrates Map behavior.
 */
class Map_DemonstrationTest extends \PHPUnit_Framework_TestCase
{
    public function test_sum_of_word_lengths()
    {
        $words = new Map(array ('bee', 'bear', 'beetle'));
        $length = $words
            ->map(function ($word) { return strlen($word); })
            ->reduce(function ($total, $length) { return $total + $length; })
        ;
        $this->assertSame(13, $length);
    }

    public function test_sum_of_odds()
    {
        $nums = new MapOfInts(range(0, 10));
        $sum = $nums->all('1 == $_0 % 2')->sum();
        $this->assertSame(25, $sum);
    }

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
