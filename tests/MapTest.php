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

    public function test_sets()
    {
        $nums = new Map();
        $nums->set(242, 0);
        $nums->push(1);
        $nums[] = 2;
        $this->assertSame([ 242 => 0, 243 => 1, 244 => 2], $nums->toArray());
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

    /**
     * @dataProvider provides_keys
     */
    public function test_common_and_exotic_keys($key)
    {
        $map = new Map;
        $map->set($key, 'foo');
        $this->assertSame('foo', $map->get($key, 'baz'));
        $this->assertCount(1, $map);
        $map->all(function ($v, $k) use ($key) {
            $this->assertSame($key, $k);
        });
    }

    /**
     * In PHP, float and boolean keys are truncated to integers, thus if you
     * want to store $a[1] and $a[1.0], you can't, without hashing the key
     * yourself. Map does not behave this way: these are distinct keys.
     *
     * @see http://php.net/manual/en/language.types.array.php
     */
    public function test_key_normalization()
    {
        $map = new Map;
        $map->set(true, 'foo');
        $map->set(1,    'bar');
        $map->set(1.0,  'baz');
        $this->assertCount(3, $map);
    }

    public function test_keys()
    {
        $keys = (new Map([ 'foo' => 0, 'bar' => 1 ]))->keys();
        $this->assertCount(2, $keys);
        $this->assertSame('foo', $keys[0]);
        $this->assertSame('bar', $keys[1]);
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

    public function test_all()
    {
        $nums = new Map(range(0, 9));
        $even = $nums->all(function ($val, $key) { return 0 == $val % 2; });
        $odds = $nums->all('1 == ($_0 % 2)');
        $this->assertSame(
            array_combine([ 0, 2, 4, 6, 8 ], [ 0, 2, 4, 6, 8 ]),
            $even->toArray()
        );
        $this->assertSame(
            array_combine([ 1, 3, 5, 7, 9 ], [ 1, 3, 5, 7, 9 ]),
            $odds->toArray()
        );
    }

    public function test_first()
    {
        $nums = new Map(range(0, 9));

        // first proper
        $mod4 = $nums->first('1 === $_0 % 4');
        $this->assertSame(1, $mod4->count());
        $this->assertSame([ 1 => 1 ], $mod4->toArray());

        // first 2 (which is neither first nor equal to # of elements)
        $mod4 = $nums->first('1 === $_0 % 4', 2);
        $this->assertSame(2, $mod4->count());
        $this->assertSame([ 1 => 1, 5 => 5 ], $mod4->toArray());

        // first 3 (which is exactly equal to # of elements)
        $mod4 = $nums->first('1 === $_0 % 4', 3);
        $this->assertSame(3, $mod4->count());
        $this->assertSame([ 1 => 1, 5 => 5, 9 => 9 ], $mod4->toArray());

        // first 4 (which is more than # of elements)
        $mod4 = $nums->first('1 === $_0 % 4', 4);
        $this->assertSame(3, $mod4->count());
        $this->assertSame([ 1 => 1, 5 => 5, 9 => 9 ], $mod4->toArray());
    }

    public function test_last()
    {
        $nums = new Map(range(0, 9));

        // last proper
        $mod4 = $nums->last('1 === $_0 % 4');
        $this->assertSame(1, $mod4->count());
        $this->assertSame([ 9 => 9 ], $mod4->toArray());

        // last 2 (which is neither last nor equal to # of elements)
        $mod4 = $nums->last('1 === $_0 % 4', 2);
        $this->assertSame(2, $mod4->count());
        $this->assertSame([ 9 => 9, 5 => 5 ], $mod4->toArray());

        // last 3 (which is exactly equal to # of elements)
        $mod4 = $nums->last('1 === $_0 % 4', 3);
        $this->assertSame(3, $mod4->count());
        $this->assertSame([ 9 => 9, 5 => 5, 1 => 1 ], $mod4->toArray());

        // last 4 (which is more than # of elements)
        $mod4 = $nums->last('1 === $_0 % 4', 4);
        $this->assertSame(3, $mod4->count());
        $this->assertSame([ 9 => 9, 5 => 5, 1 => 1 ], $mod4->toArray());
    }

    public function test_every_some_none()
    {
        $nums = new Map(range(0, 9));
        $this->assertTrue(
            $nums->every('is_int($_0) && is_int($_1)')
        );
        $this->assertTrue(
            $nums->some('0 === $_0 % 2')
        );
        $this->assertTrue(
            $nums->none('$_0 < 0')
        );
    }

    public function test_push_pop()
    {
        $num1 = rand();
        $num2 = rand();

        $nums = new Map();
        $nums->push($num1);
        $this->assertCount(1, $nums);
        $nums->push($num2);
        $this->assertCount(2, $nums);
        $popped = $nums->pop();
        $this->assertCount(1, $nums);
        $this->assertSame($num2, $popped);
        $popped = $nums->pop();
        $this->assertCount(0, $nums);
        $this->assertSame($num1, $popped);
    }

    public function test_vacuous_pop()
    {
        $map = new Map;
        $this->assertNull($map->pop());
    }

    public function test_rekey()
    {
        $freq = new Map(count_chars('madam im adam', 1));
        $lets = $freq->rekey('chr($_1)');
        $this->assertSame(
            [ ' ' => 2, 'a' => 4, 'd' => 2, 'i' => 1, 'm' => 4 ],
            $lets->toArray()
        );
    }

    public function test_rekey_with_exotics()
    {
        $fds = (new Map(range(0, 2)))->rekey(function ($number) {
            switch ($number) {
            case 0: return fopen('php://stdin', 'r');
            case 1: return fopen('php://stdout', 'w');
            case 2: return fopen('php://stderr', 'w');
            }
        });
        $this->assertCount(3, $fds);
        $fds->all(function ($id, $fd) {
            $this->assertInternalType('int', $id);
            $this->assertInternalType('resource', $fd);
        });
    }

    public function test_into()
    {
        $map = new Map();
        $this->assertInstanceOf(
            '\Haldayne\Boost\MapOfStrings',
            $map->into(new MapOfStrings)
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
        $this->assertInstanceOf('\Traversable', $i);

        $count = 0;
        foreach ($c as $key => $value) {
            $this->assertEquals($a[$key], $value);
            $count++;
        }

        $this->assertEquals(count($a), $count);
    }

    public function test_iterator_mutation()
    {
        $values = [
            'a' => new Map(),
            'b' => new Map(),
        ];
        $map = new Map($values);

        foreach ($map as $value) {
            $this->assertInstanceOf(Map::class, $value);
        }
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

    public static function provides_keys()
    {
        return [
            [ null ],
            [ true ],
            [ false ],
            [ 0 ],
            [ -1 ],
            [ 1 ],
            [ 0.0 ],
            [ -1.1 ],
            [ 1.1 ],
            [ '' ],
            [ 'foo' ],
            [ [] ],
            [ [ 'foo' ] ],
            [ new \StdClass ],
            [ fopen('php://stdout', 'w') ],
            [ function () { } ],
        ];
    }
}
