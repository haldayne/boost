<?php
namespace Haldayne\Boost;

class MapOfObjectsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provides_values
     */
    public function test_guard($value, $valid)
    {
        if (! $valid) {
            $this->setExpectedException('\UnexpectedValueException');
        }
        $map = new MapOfObjects();
        $map->push($value);
    }

    public function test_apply()
    {
        $dates = new MapOfObjects([
            'jkab' => \DateTime::createFromFormat('j-M-Y', '17-Oct-1978'),
            'fpab' => \DateTime::createFromFormat('j-M-Y', '13-May-2013'),
        ]);
        $result = $dates->apply('add', [ new \DateInterval('P1Y') ]);
        $this->assertSame('17-Oct-1979', $result['jkab']->format('j-M-Y'));
        $this->assertSame('13-May-2014', $result['fpab']->format('j-M-Y'));
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
            [ 'foo', false ],
            [ new \StdClass, true ],
            [ new Map, true ],
        ];
    }
}
