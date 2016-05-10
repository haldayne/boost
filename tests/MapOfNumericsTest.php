<?php
namespace Haldayne\Boost;

class MapOfNumericsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provides_methods_without_defined_results
     * @expectedException \RangeException
     */
    public function test_no_defined_value($method, array $args)
    {
        $map = new MapOfNumerics;
        call_user_func_array([ $map, $method ], $args);
    }

    /**
     * @dataProvider provides_inputs_methods_and_results
     */
    public function test_method(array $source, $method, array $args, $result)
    {
        $map = new MapOfNumerics($source);
        $this->assertSame(
            $result,
            call_user_func_array([ $map, $method ], $args)
        );
    }

    public function test_increment()
    {
        $map = new MapOfNumerics;
        $this->assertSame(0, $map->sum());

        $map->increment('foo');
        $this->assertSame(1, $map->sum());
        $this->assertSame(1, $map->get('foo'));

        $map->increment('bar');
        $this->assertSame(2, $map->sum());
        $this->assertSame(1, $map->get('foo'));
        $this->assertSame(1, $map->get('bar'));

        $map->increment('foo', -1);
        $this->assertSame(1, $map->sum());
        $this->assertSame(0, $map->get('foo'));
        $this->assertSame(1, $map->get('bar'));

        $map->increment('bar', -1);
        $this->assertSame(0, $map->sum());
        $this->assertSame(0, $map->get('foo'));
        $this->assertSame(0, $map->get('bar'));

        $map->increment('baz', 7);
        $this->assertSame(7, $map->sum());
        $this->assertSame(0, $map->get('foo'));
        $this->assertSame(0, $map->get('bar'));
        $this->assertSame(7, $map->get('baz'));
    }

    public function test_decrement()
    {
        $map = new MapOfNumerics;
        $this->assertSame(0, $map->sum());

        $map->decrement('foo');
        $this->assertSame(-1, $map->sum());
        $this->assertSame(-1, $map->get('foo'));

        $map->decrement('bar');
        $this->assertSame(-2, $map->sum());
        $this->assertSame(-1, $map->get('foo'));
        $this->assertSame(-1, $map->get('bar'));

        $map->decrement('foo', -1);
        $this->assertSame(-1, $map->sum());
        $this->assertSame(0, $map->get('foo'));
        $this->assertSame(-1, $map->get('bar'));

        $map->decrement('bar', -1);
        $this->assertSame(0, $map->sum());
        $this->assertSame(0, $map->get('foo'));
        $this->assertSame(0, $map->get('bar'));

        $map->decrement('baz', 7);
        $this->assertSame(-7, $map->sum());
        $this->assertSame(0, $map->get('foo'));
        $this->assertSame(0, $map->get('bar'));
        $this->assertSame(-7, $map->get('baz'));
    }

    // -=-= Data Providers =-=-

    public static function provides_methods_without_defined_results()
    {
        return [
            [ 'mean', [] ],
            [ 'min',  [] ],
            [ 'max',  [] ],
        ];
    }

    public static function provides_inputs_methods_and_results()
    {
        return [
            [ range(1, 5),  'sum',     [],  15 ], // (n(n+1))/2 = 55
            [ range(1, 5),  'product', [], 120 ], // https://oeis.org/A000142
            [ range(1, 5),  'mean',    [],   3 ],
            [ range(1, 10), 'min',     [],   1 ],
            [ range(1, 10), 'max',     [],  10 ],
        ];
    }
}
