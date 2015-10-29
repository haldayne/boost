<?php
namespace Haldayne\Boost\Lambda;

class YTest extends \PHPUnit_Framework_TestCase
{
    public function test_with_factorial()
    {
        $factorial = new Y(function ($factorial) {
            return function($n) use ($factorial) {
                return $n <= 1 ? 1 : ($n*$factorial($n-1));
            };
        });
        $this->assertSame(720, $factorial(6));
    }

    public function x_test_with_fibonacci()
    {
        $fibonacci = new Y(function ($fibonacci) {
            return function ($n) use ($fibonacci) {
                switch ($n) {
                case 0:  return 0;
                case 1:  return 1;
                default: return $fibonacci($n-1) + $fibonacci($n-2);
                }
            };
        });
        $this->assertSame(8, $fibonacci(6));
    }
}
