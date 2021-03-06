<?php
namespace Haldayne\Boost;

class MapOfStringsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provides_values
     */
    public function test_guard($value, $valid)
    {
        if (! $valid) {
            $this->setExpectedException('\UnexpectedValueException');
        }
        $map = new MapOfStrings();
        $map->push($value);
    }

    public function test_join()
    {
        $map = new MapOfStrings([ 'bleak', 'house' ]);
        $this->assertSame('bleak house', $map->join(' '));
    }

    public function test_letter_frequency()
    {
        $words = new MapOfStrings(explode(' ', 'the cat in the hat'));
        $freqs = $words->letter_frequency();
        $this->assertInstanceOf('\Haldayne\Boost\MapOfInts', $freqs);
        $this->assertEquals(
            [
                'a' => 2,
                'c' => 1,
                'e' => 2,
                'h' => 3,
                'i' => 1,
                'n' => 1,
                't' => 4,
            ],
            $freqs->toArray()
        );
    }

    /**
     * We've got a map of strings, and we should be able to call keys on it
     * and get the keys out, even if the keys aren't themselves strings.
     */
    public function test_keys_with_exotic_keys()
    {
        $map = new MapOfStrings;
        $map['foo']          = 'bar';
        $map[function () {}] = 'baz';
        $map->keys();
    }

    // -=-= Data Providers =-=-

    public static function provides_values()
    {
        return [
            [ true, false ],
            [ 1, false ],
            [ 1.0, false ],
            [ fopen('php://memory', 'r'), false ],
            [ [], false ],
            [ new \StdClass, false ],
            [ new Map, false ],
            [ 'foo', true ],
        ];
    }
}
