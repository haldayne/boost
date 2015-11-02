<?php
namespace Haldayne\Boost;

class MapOfMapsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \UnexpectedValueException
     */
    public function test_guard()
    {
        new MapOfMaps([ 1 ]);
    }

    /**
     */
}
