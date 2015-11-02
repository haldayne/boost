<?php
namespace Haldayne\Boost;

/**
 * Demonstrates Map behavior in the form of tests.
 */
class MapUseCaseTest extends \PHPUnit_Framework_TestCase
{
    public function test_sum_of_odds()
    {
        $nums = new Map(range(0, 9));
        $this->assertSame(
            25,
            $nums->filter('1 === $_0 % 2')->reduce('$_0 + $_1')
        );
    }

    public function test_product_of_doubles()
    {
        $nums = new Map(range(1, 5));
        $this->assertSame(
            3840,
            $nums->map('$_0 * 2')->reduce('$_0 * $_1', 1)
        );
    }

    public function test_total_count_chars()
    {
        $titles = new Map([ 'abc', 'cba' ]);
        $counts = $titles
            ->map(function ($string) { return count_chars($string, 1); })
            ->reduce(
                function ($total, $counts) {
                    return $total += $counts;
                },
                [],
                function ($reduced) {
                    return new Map($reduced);
                }
            )
            ->rekey(function ($value, $key) { return chr($key); });
        ;
        $this->assertSame(
            [ 'a' => 2, 'b' => 2, 'c' => 2],
            $counts->toArray()
        );
    }
}
