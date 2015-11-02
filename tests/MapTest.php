<?php
namespace Haldayne\Boost;

class MapTest extends \PHPUnit_Framework_TestCase
{
    /** @dataProvider provides_valid_collection */
    public function test_construction_valid($collection)
    {
        new Map($collection);
    }

    /**
     * @dataProvider provides_invalid_collection
     * @expectedException InvalidArgumentException
     */
    public function test_construction_invalid($collection)
    {
        new Map($collection);
    }

    public function test_has_get_set_forget()
    {
        $c = new Map([ 'a', 'b', 'c' ]);
        $this->assertTrue($c->has(0));
        $this->assertFalse($c->has(5));
        $r = $c->forget(0);
        $this->assertSame($c, $r);
        $this->assertFalse($c->has(0));
        $this->assertSame('b', $c->get(1));
        $r = $c->set(1, 'B');
        $this->assertSame($c, $r);
        $this->assertSame('B', $c->get(1));
        $c->set(5, 'C');
        $this->assertTrue($c->has(5));
    }

    /** @dataProvider provides_valid_collection */
    public function test_isEmpty($collection, array $array)
    {
        $c = new Map($collection);
        $this->assertSame(
            0 === count($array),
            $c->isEmpty()
        );
    }

    public function test_diff()
    {
        $c = new Map(['apple', 'banana', 'a' => 'apple', 'b' => 'banana']);

        $x = $c->diff(['apple']);
        $this->assertSame([1 => 'banana', 'b' => 'banana'], $x->toArray());

        $x = $c->diff([0 => 'apple'], Map::LOOSE);
        $this->assertSame([1 => 'banana', 'b' => 'banana'], $x->toArray());

        $x = $c->diff(['a' => 'apple'], Map::STRICT);
        $this->assertSame([0 => 'apple', 1 => 'banana', 'b' => 'banana'], $x->toArray());
    }

    public function test_intersect()
    {
        $c = new Map(['apple', 'banana', 'a' => 'apple', 'b' => 'banana']);

        $x = $c->intersect([0 => 'apple']);
        $this->assertSame([0 => 'apple', 'a' => 'apple'], $x->toArray());

        $x = $c->intersect([0 => 'apple'], Map::LOOSE);
        $this->assertSame([0 => 'apple', 'a' => 'apple'], $x->toArray());

        $x = $c->intersect(['a' => 'apple'], Map::STRICT);
        $this->assertSame(['a' => 'apple'], $x->toArray());
    }

    public function test_walk()
    {
        $c = new Map(range(5, 9));
        $c->walk(function ($value, $key) use (&$key_sum, &$value_sum) {
            $key_sum += $key;
            $value_sum += $value;
        });
        $this->assertSame(10, $key_sum);
        $this->assertSame(35, $value_sum);

        $c = new Map(range(1, 3));
        $c->walk(function ($value, $key) use (&$x) {
            if (1 === $key) {
                return false;
            } else {
                $x = [ $key => $value ];
                return 0;
            }
        });
        $this->assertSame([ 0 => 1 ], $x);
    }

    public function test_partition()
    {
        // by value
        $c = new Map([ 'fruit-apple', 'fruit-banana', 'veggie-cabbage' ]);
        $x = $c->partition(function ($value) {
            list ($kind, $name) = explode('-', $value);
            return $kind;
        });
        $this->assertSame(
            [
                'fruit' => [ 'fruit-apple', 'fruit-banana' ],
                'veggie' => [ 2 => 'veggie-cabbage' ]
            ],
            $x->toArray()
        );

        // by key
        $c = new Map(range(0, 3));
        $x = $c->partition(function ($value, $key) {
            return 0 === ($key % 2) ? 'even' : 'odd';
        });
        $this->assertSame(
            [
                'even' => [ 0 => 0, 2 => 2 ],
                'odd' => [ 1 => 1, 3 => 3 ],
            ],
            $x->toArray()
        );
    }

    // tests for implements \Countable

    /** @dataProvider provides_valid_collection */
    public function test_count($collection, array $array)
    {
        $this->assertSame(count($array), (new Map($collection))->count());
    }

    // tests for implements Arrayable

    /** @dataProvider provides_valid_collection */
    public function test_toArray($collection, array $array)
    {
        $c = new Map($collection);
        $this->assertSame($array, $c->toArray());
    }

    // tests for implements Jsonable

    /** @dataProvider provides_valid_collection */
    public function test_toJson($collection, array $array)
    {
        $c = new Map($collection);
        $this->assertSame(json_encode($array), $c->toJson());
    }

    // tests for implements \ArrayAccess

    public function test_array_access()
    {
        $c = new Map([ 'a', 'b', 'c' ]);
        $this->assertTrue(isset($c[0]));
        $this->assertFalse(isset($c[5]));
        unset($c[0]);
        $this->assertFalse(isset($c[0]));
        $this->assertSame('b', $c[1]);
        $c[1] = 'B';
        $this->assertSame('B', $c[1]);
        $c[5] = 'C';
        $this->assertTrue(isset($c[5]));
        $this->assertSame('C', $c[5]);
    }

    // tests for implements \IteratorAggregate

    public function test_iterator_aggregate()
    {
        $a = [ 'a', 'b' ];
        $c = new Map($a);
        $i = $c->getIterator();
        $this->assertInstanceOf('\ArrayIterator', $i);
        $this->assertEquals($a, $i->getArrayCopy());
    }


    // -=-= Data Providers =-=-

    /**
     * Provides a structure to initialize a Map, and the corresponding
     * native PHP array.
     */
    public static function provides_valid_collection()
    {
        return [
            [ null, [] ],
            [ [], [] ],
            [ new Map(), [] ],
            [ new \StdClass(), [] ],
            [ range(1, 5), range(1, 5) ],
            [ new Map(range(1, 5)), range(1, 5) ],
        ];
    }

    /**
     * Provides types that cannot be made into a Map.
     */
    public static function provides_invalid_collection()
    {
        return [
            [ true ],
            [ 242 ],
            [ 2.42 ],
            [ 'string' ],
            [ fopen('php://memory', 'r') ],
        ];
    }
}
