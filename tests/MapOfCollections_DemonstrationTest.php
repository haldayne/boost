<?php
namespace Haldayne\Boost;

/**
 * Demonstrates MapOfCollections behavior.
 */
class MapOfCollections_DemonstrationTest extends \PHPUnit_Framework_TestCase
{
    public function test_record_pluck()
    {
        $map = new MapOfCollections;
        $map->push([ 'id' => 5, 'name' => 'Ada', 'age' => 16 ]);
        $map->push([ 'id' => 6, 'name' => 'Bee', 'age' => 12 ]);
        $map->push([ 'id' => 7, 'name' => 'Cam', 'age' => 37 ]);
        $this->assertSame(
            [ 5=>'Ada', 6=>'Bee', 7=>'Cam' ],
            $map->pluck('name', 'id')->toArray()
        );
    }
}
