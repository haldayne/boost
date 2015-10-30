<?php
namespace Haldayne\Boost\Lambda;

class RetryTest extends \PHPUnit_Framework_TestCase
{
    public function test_returns_immediately_with_result_code()
    {
        $lambda = new Retry(function () { return true; });
        $result = $lambda();
        $this->assertSame(true, $result);
    }

    public function test_backs_off_until_true()
    {
        $n = 0;
        $c = 0;
        $lambda = new Retry(function () use (&$n) { return 2 == $n++ ? true : false; });
        $lambda->setStrategy(function ($n) use (&$c) { $c++; });
        $lambda->setAttempts(3);
        $result = $lambda();
        $this->assertSame(true, $result);
        $this->assertSame(2, $c);
    }

    public function test_backs_off_to_limit()
    {
        $c = 0;
        $lambda = new Retry(function () { return false; });
        $lambda->setStrategy(function ($n) use (&$c) { $c++; });
        $lambda->setAttempts(5);
        $result = $lambda();
        $this->assertSame(false, $result);
        $this->assertSame(4, $c); // one fewer than maximum attempts: strategy
                                  // not called for final failure
    }
}
