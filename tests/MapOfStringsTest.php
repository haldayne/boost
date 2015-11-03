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

    public function test_split()
    {
        $map = MapOfStrings::split(' ', 'war of the worlds');
        $this->assertSame(4, $map->count());
        $this->assertSame('the', $map[2]);
    }

    public function test_join()
    {
        $map = new MapOfStrings([ 'bleak', 'house' ]);
        $this->assertSame('bleak house', $map->join(' '));
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
