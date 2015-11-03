<?php
namespace Haldayne\Boost;

class GuardedMapAbstractTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // create a sut that accepts odd numbers but rejects even numbers
        $this->sut = $this->getMockForAbstractClass('\Haldayne\Boost\GuardedMapAbstract');
        $this->sut
            ->expects($this->any())
            ->method('allowed')
            ->will($this->returnCallback(function ($n) {
                return $n & 1 ? true : false;
            }))
        ;
    }

    public function test_guard_valid()
    {
        // try all the ways we can set into a map, give it an odd number
        $this->sut->set(0, 43);
        $this->sut->push(45);
        $this->sut[] = 47;
        $this->assertSame([ 43, 45, 47 ], $this->sut->toArray());
    }

    public function test_guard_valid_with_falsey()
    {
        // test the allowed method can return false-like values (not boolean
        // false) and still pass
        $falseys = [ 0, 0.0, '', [], new \StdClass ];
        foreach ($falseys as $falsey) {
            $sut = $this->getMockForAbstractClass('\Haldayne\Boost\GuardedMapAbstract');
            $sut->expects($this->any())
                ->method('allowed')
                ->will($this->returnValue($falsey))
            ;
            $sut->push('foo');
        }
    }

    public function test_guard_invalid_set()
    {
        $this->setExpectedException('\UnexpectedValueException');
        $this->sut->set(0, 42);
    }

    public function test_guard_invalid_push()
    {
        $this->setExpectedException('\UnexpectedValueException');
        $this->sut->push(42);
    }

    public function test_guard_invalid_append()
    {
        $this->setExpectedException('\UnexpectedValueException');
        $this->sut[] = 42;
    }

    public function test_normalization()
    {
        $sut = $this->getMockForAbstractClass(
            '\Haldayne\Boost\GuardedMapAbstract',
            [], '', true, true, true,
            [ 'normalize' ]
        );
        $sut->expects($this->any())
            ->method('normalize')
            ->will($this->returnValue('bar'))
        ;
        $sut->push('foo');
        $this->assertSame('bar', $sut->pop());
    }
}
