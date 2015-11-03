<?php
namespace Haldayne\Boost;

class MapOfCollectionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provides_values
     */
    public function test_guard($value, $valid)
    {
        if (! $valid) {
            $this->setExpectedException('\UnexpectedValueException');
        }
        $map = new MapOfCollections();
        $map->push($value);
    }

    public function test_pluck()
    {
        $map = new MapOfCollections([ [1, 2, 3], [4, 5, 6], [7, 8, 9] ]);
        $this->assertSame(
            [ 2, 5, 8 ],
            $map->pluck(1)->toArray()
        );
    }

    // -=-= Data Providers =-=-

    public static function provides_values()
    {
        return [
            [ true, false ],
            [ 1, false ],
            [ 1.0, false ],
            [ 'foo', false ],
            [ fopen('php://memory', 'r'), false ],
            [ [], true ],
            [ new \StdClass, true ],
            [ new Map, true ],
        ];
    }
}
